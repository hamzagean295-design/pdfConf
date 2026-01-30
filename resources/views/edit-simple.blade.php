<x-app-layout>
    @push('scripts')
        <script>
                const overlay = document.getElementById('pdf-overlay');
                const cordX = document.getElementById('cord-x');
                const cordY = document.getElementById('cord-y');
                const myCursor = document.getElementById('my-cursor');

                myCursor.addEventListener('click', function() {
                        overlay.addEventListener('mousemove', updateCords);
                        overlay.classList.add('cursor-crosshair');
                        overlay.classList.remove('pointer-events-none');
                        overlay.classList.add('pointer-events-auto');

                });

                function convertToMm(pxValue, isWidth = true) {
                    const overlay = document.getElementById('pdf-overlay');
                    const rect = overlay.getBoundingClientRect();

                    // 1. Dimensions théoriques d'un A4 en mm
                    const A4_WIDTH_MM = 210;
                    const A4_HEIGHT_MM = 297;

                    // 2. Calcul du ratio (Combien de mm représente 1 pixel à l'écran ?)
                    // On divise la taille réelle (mm) par la taille affichée (px)
                    const mmPerPxWidth = A4_WIDTH_MM / rect.width;
                    const mmPerPxHeight = A4_HEIGHT_MM / rect.height;

                    // 3. Application du ratio selon l'axe
                    if (isWidth) {
                        return (pxValue * mmPerPxWidth).toFixed(2);
                    } else {
                        return (pxValue * mmPerPxHeight).toFixed(2);
                    }
                }

                function updateCords(e) {
                    const rect = overlay.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const PDF_VIEWER_HEADER_HEIGHT = 56; // px
                    const y = e.clientY - rect.top - PDF_VIEWER_HEADER_HEIGHT;
                    cordX.value = convertToMm(x);
                    cordY.value = convertToMm(y);
                    cordX.dispatchEvent(new Event('input', { bubbles: true }));
                    cordY.dispatchEvent(new Event('input', { bubbles: true }));

                }

                overlay.addEventListener('click', (event) => {
                    overlay.removeEventListener('mousemove', updateCords);
                    overlay.classList.remove('cursor-crosshair');
                    overlay.classList.remove('pointer-events-auto');
                    overlay.classList.add('pointer-events-none');
                });

            function formEditor(config) {
                return {
                    // --- STATE ---
                    elements: [],
                    selectedId: null,
                    documentId: config.documentId,
                    totalPages: config.totalPages,
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
                        if (!this.selectedId) return null;
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
                            value: 'Texte statique',
                            page: 1,
                            x: 10, y: 10,
                            font_family: 'Helvetica', font_style: 'normal', font_size: 12,
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
                                            const { id, hexColor, ...rest } = el; // Remove temporary frontend properties
                                            if (rest.type !== 'checkbox') {
                                                delete rest.options; // Clean up options for non-checkboxes
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
                }
            }
        </script>
    @endpush

    <div class="py-4  overflow-y-scroll">
        <h1 class="text-center pb-2 text-xl font-bold"> Configurer votre modèle {{ $document->name }} </h1>
        Configurer votre modèle
        <div x-data="formEditor({
                elements: {{ Illuminate\Support\Js::from($document->config['elements'] ?? []) }},
                documentId: {{ $document->id }},
                totalPages: {{ $totalPages }}
            })" x-cloak class="flex h-screen bg-gray-100">

            <!-- Colonne de gauche : Liste des éléments -->
            <aside class="w-1/4 h-full flex flex-col border-r bg-white">
                <div class="p-4 border-b">
                    <button @click="addElement()" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded cursor-pointer">
                        Ajouter un Élément
                    </button>
                </div>
                <div class="flex-grow overflow-y-auto">
                    <template x-for="element in elements" :key="element.id">
                        <div @click="selectElement(element.id)"
                            class="p-3 border-b cursor-pointer hover:bg-gray-50 flex justify-between items-center"
                            :class="{ 'bg-blue-100 hover:bg-blue-100': selectedId === element.id }">
                            <div>
                                <!-- Affiche le label ou la valeur si pas de label -->
                                <p class="font-semibold" x-text="element.label || element.value"></p>
                                <p class="text-sm text-gray-600">
                                    Type: <span x-text="element.type"></span>,
                                    Page: <span x-text="element.page"></span>
                                </p>
                            </div>
                            <button @click.stop="removeElement(element.id)" class="text-red-500 hover:text-red-700 text-xl">&times;</button>
                        </div>
                    </template>
                </div>
            </aside>

            <!-- Colonne du centre : Prévisualisation PDF -->
            <main class="relative w-1/2 h-full flex flex-col bg-gray-200">
                <!-- Le PDF (Interactions normales par défaut) -->
                <embed src="{{ $pdfUrl }}" type="application/pdf" class="w-full h-full">

                <!-- L'overlay (Bloque les interactions seulement quand c'est nécessaire) -->
                <div id="pdf-overlay"
                    class="absolute inset-0 z-10 bg-transparent pointer-events-none">
                </div>
            </main>
            <!-- Colonne de droite : Éditeur de propriétés -->
            <aside class="w-1/4 h-full flex flex-col border-l bg-white">
                <div class="p-4 overflow-y-auto">
                    <h2 class="text-lg font-bold mb-4">Propriétés</h2>
                    <div x-show="!selectedId" class="text-gray-500">
                        Sélectionnez un élément à gauche pour l'éditer.
                    </div>
                    <div x-show="selectedId" x-if="selectedElement()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Label (pour l'éditeur)</label>
                            <input type="text" x-model="selectedElement().label" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Type</label>
                            <select x-model="selectedElement().type" @change="if(selectedElement().type === 'checkbox' && !selectedElement().options) selectedElement().options = []" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="text">Texte Statique</option>
                                <option value="tag">Tag Dynamique</option>
                                <option value="checkbox">Case à cocher</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Page</label>
                            <select x-model.number="selectedElement().page" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <template x-for="i in totalPages" :key="i">
                                    <option :value="i" x-text="i"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Valeur / Tag</label>
                            <input type="text" x-model.debounce.200ms="selectedElement().value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" :placeholder="selectedElement().type === 'checkbox' ? 'ex: @{{user.gender}}' : 'Texte ou @{{tag}}'">
                        </div>
                        <div class="grid grid-cols-3 gap-4" x-show="selectedElement().type !== 'checkbox'">
                            <div>
                                <label class="block text-sm font-medium">X (mm)</label>
                                <input type="number" step="0.1" id="cord-x" x-model.number="selectedElement().x" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Y (mm)</label>
                                <input type="number" step="0.1" id="cord-y" x-model.number="selectedElement().y" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <button id="my-cursor" type="button" class="bg-green-300 p-1 border rounded-sm self-end mb-1">Définir</button>
                        </div>

                        <!-- Champs de style (communs) -->
                        <div class="border-t pt-4 mt-4 space-y-4">
                            <h3 class="font-semibold" x-text="selectedElement().type === 'checkbox' ? 'Style du marqueur (X)' : 'Style du texte'"></h3>
                            <div>
                                <label class="block text-sm font-medium">Police</label>
                                <input type="text" x-model.debounce.200ms="selectedElement().font_family" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Taille Police</label>
                                <input type="number" x-model.number="selectedElement().font_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Poids Police</label>
                                <select x-model="selectedElement().font_weight" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="normal">Normal</option>
                                    <option value="bold">Gras</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Style Police</label>
                                <select x-model="selectedElement().font_style" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="normal">Normal</option>
                                    <option value="italic">Italique</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Couleur</label>
                                <input type="color" :value="selectedElement().hexColor" @input="updateColor($event)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm h-10">
                            </div>
                        </div>

                        <!-- Éditeur d'options pour Checkbox -->
                        <div x-show="selectedElement().type === 'checkbox'" class="border-t pt-4 mt-4 space-y-3">
                            <h3 class="font-semibold">Options de la case à cocher</h3>
                            <p class="text-xs text-gray-500">Définissez les options possibles. La valeur du tag ci-dessus sera comparée à la "valeur attendue" de chaque option pour décider où placer le marqueur.</p>
                            <button @click="addCheckboxOption()" type="button" class="w-full bg-blue-500 text-white font-bold py-1 px-3 rounded text-sm hover:bg-blue-600">
                                Ajouter une option
                            </button>
                            <template x-for="(option, index) in selectedElement().options" :key="index">
                                <div class="border p-2 rounded space-y-2 bg-gray-50">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-sm" x-text="`Option ${index + 1}`"></span>
                                        <button @click="removeCheckboxOption(index)" type="button" class="text-red-500 hover:text-red-700 text-xl leading-none">&times;</button>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium">Label (pour l'éditeur)</label>
                                        <input type="text" x-model="option.label" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium">Valeur attendue</label>
                                        <input type="text" x-model="option.value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium">X (mm)</label>
                                            <input type="number" step="0.1" x-model.number="option.x" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium">Y (mm)</label>
                                            <input type="number" step="0.1" x-model.number="option.y" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t mt-auto">
                    <button @click="save()" :disabled="isSaving" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded disabled:bg-gray-400 cursor-pointer">
                        <span x-show="!isSaving">Sauvegarder la Configuration</span>
                        <span x-show="isSaving">Sauvegarde...</span>
                    </button>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
