document.addEventListener('alpine:init', () => {
    Alpine.data('formEditor', (config) => ({
        // --- STATE ---
        elements: [],
        selectedId: null,
        documentId: config.documentId,
        totalPages: config.totalPages,
        fonts: config.fonts,
        isSaving: false,

        // --- INIT ---
        init() {
            this.elements = config.elements.map((el, index) => {
                let type = el.type;
                if (!type) { // For backward compatibility
                    if (el.options && Array.isArray(el.options)) {
                        type = 'checkbox';
                    } else if (el.value && el.value.match(/^\{\{.*\}\}$/)) {
                        type = 'tag';
                    } else {
                        type = 'text';
                    }
                }
                return {
                    ...el,
                    id: Date.now() + index,
                    label: el.label || `Élément ${index + 1}`, // Add a default label
                    page: el.page || 1,
                    font_weight: el.font_weight || 'normal',
                    font_style: el.font_style || 'normal',
                    color: el.color || [0, 0, 0],
                    type: type,
                    // Ensure options is an array for checkboxes, and undefined otherwise
                    options: type === 'checkbox' ? (el.options || []) : undefined
                };
            });
        },

        // --- GETTERS & HELPERS ---
        selectedElement() {
            if (!this.selectedId) return {};
            const element = this.elements.find(el => el.id === this.selectedId);
            if (element) {
                element.hexColor = this.rgbArrayToHex(element.color);
            }
            return element;
        },

        rgbArrayToHex(rgbArray) {
            if (!rgbArray || rgbArray.length !== 3) return '#000000';
            return '#' + rgbArray.map(c => ('0' + c.toString(16)).slice(-2)).join('');
        },

        hexToRgbArray(hexString) {
            if (!hexString) return [0, 0, 0];
            const hex = hexString.replace(/^#/, '');
            const bigint = parseInt(hex, 16);
            return [(bigint >> 16) & 255, (bigint >> 8) & 255, bigint & 255];
        },

        // --- ACTIONS ---
        selectElement(id) {
            this.selectedId = id;
        },
        addElement() {
            const newElement = {
                id: Date.now(),
                type: 'text',
                label: 'Nouvel élément',
                value: '',
                valueTest: '',
                page: 1,
                x: 10,
                y: 10,
                font_family: 'Arial',
                font_style: 'normal',
                font_size: 12,
                font_weight: 'normal',
                color: [0, 0, 0]
            };
            this.elements.push(newElement);
            this.selectElement(newElement.id);
        },
        removeElement(id) {
            this.elements = this.elements.filter(el => el.id !== id);
            if (this.selectedId === id) {
                this.selectedId = null;
            }
        },
        updateColor(event) {
            if (this.selectedElement()) {
                this.selectedElement().color = this.hexToRgbArray(event.target.value);
            }
        },
        addCheckboxOption() {
            const el = this.selectedElement();
            if (el && el.type === 'checkbox') {
                if (!el.options) {
                    el.options = [];
                }
                // Add a new option, staggering the Y coordinate for visibility
                const yOffset = el.options.length * 8;
                el.options.push({
                    label: 'Nouvelle option',
                    value: 'valeur_a_verifier',
                    x: el.x || 10,
                    y: (el.y || 10) + yOffset
                });
            }
        },
        removeCheckboxOption(index) {
            const el = this.selectedElement();
            if (el && el.type === 'checkbox' && el.options) {
                el.options.splice(index, 1);
            }
        },
        async save() {
            this.isSaving = true;
            const url = `/documents/${this.documentId}/config`;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        config: {
                            elements: this.elements.map(el => {
                                const {
                                    id,
                                    hexColor,
                                    ...rest
                                } = el; // Remove temporary frontend properties
                                if (rest.type !== 'checkbox') {
                                    delete rest
                                    .options; // Clean up options for non-checkboxes
                                }
                                return rest; // Send the rest, including the 'color' array
                            })
                        }
                    })
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'La sauvegarde a échoué.');
                }
                alert('Configuration sauvegardée !');
            } catch (error) {
                console.error('Save error:', error);
                alert('Une erreur est survenue: ' + error.message);
            } finally {
                this.isSaving = false;
            }
        },
    }));
});
