@extends('layouts.app')

@push('scripts')
    <script type="module">
        // If absolute URL from the remote server is provided, configure the CORS
        // header on that server.
        var url = '{!! $pdfUrl !!}';
        var pageCount = '{{ $totalPages }}';
        pageCount = parseInt(pageCount);
        console.log(pageCount);

        var {
            pdfjsLib
        } = globalThis;

        // The workerSrc property shall be specified.
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@5.4.530/build/pdf.worker.mjs';

        const Y_FROM_TOP = true;
        // ============================================

        var canvas = document.getElementById('the-canvas');
        const demarrer = document.getElementById('demarrer');
        const cordX = document.getElementById('cord-x');
        const cordY = document.getElementById('cord-y');
        let currentPage = document.getElementById('currentPage');
        const prevPage = document.getElementById('prevPage');
        const nextPage = document.getElementById('nextPage');
        const GLOBAL_SCALE = 1;

        let VIEWPORT;
        let VIEWPORT_ORIGINAL;
        let PDF_DOCUMENT;
        let currentPdfPage;
        var loadingTask = pdfjsLib.getDocument(url);
        var pageNumber = 1;

        function init() {
            loadingTask.promise.then(function(pdf) {
                console.log('PDF loaded');
                PDF_DOCUMENT = pdf;

                // Prepare canvas using PDF page dimensions
                pdf.getPage(pageNumber).then(function(page) {
                    console.log('Page loaded');
                    currentPdfPage = page;

                    // Viewport avec scale = 1 (dimensions originales du PDF)
                    var viewportOriginal = page.getViewport({
                        scale: 1
                    });
                    VIEWPORT_ORIGINAL = viewportOriginal;

                    // Viewport avec scale pour l'affichage
                    var viewport = page.getViewport({
                        scale: GLOBAL_SCALE
                    });
                    VIEWPORT = viewport;

                    var context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    // Render PDF page into canvas context
                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    var renderTask = page.render(renderContext);
                    renderTask.promise.then(function() {
                        console.log('Page rendered');
                        console.log('Viewport (scaled):', VIEWPORT);
                        console.log('Viewport (original):', VIEWPORT_ORIGINAL);
                        console.log('Canvas size:', canvas.width, 'x', canvas.height);

                        // Afficher la hauteur de la page
                        const heightMm = (VIEWPORT_ORIGINAL.height * 25.4 / 72).toFixed(2);
                        const pageHeightEl = document.getElementById('pageHeight');
                        if (pageHeightEl) {
                            pageHeightEl.textContent = heightMm;
                        }

                        // Mettre à jour le texte d'indication
                        const coordSystemEl = document.getElementById('coordSystem');
                        if (coordSystemEl) {
                            coordSystemEl.textContent = Y_FROM_TOP ?
                                'Y=0 en haut de la page' :
                                'Y=0 en bas de la page (standard PDF)';
                        }
                    });
                });
            }, function(reason) {
                // PDF loading error
                console.error(reason);
            });

            currentPage.innerHTML = pageNumber;
        }

        // WARNING: start
        init();

        prevPage.addEventListener('click', function() {
            if (pageNumber <= 1) return;
            pageNumber -= 1;
            init();
        });

        nextPage.addEventListener('click', function() {
            if (pageNumber >= pageCount) return;
            pageNumber += 1;
            init();
        });

        function convertToMm(xCanvas, yCanvas) {
            // Constante de conversion: 1 point PDF = 25.4/72 mm
            const MM_PER_POINT = 25.4 / 72;

            // 1. Convertir les coordonnées canvas en points PDF
            const xPoints = xCanvas / GLOBAL_SCALE;

            // Pour Y: choisir le système de coordonnées
            let yPoints;
            if (Y_FROM_TOP) {
                // Y depuis le haut de la page (0 = haut)
                yPoints = yCanvas / GLOBAL_SCALE;
            } else {
                // Y depuis le bas de la page (0 = bas) - Standard PDF
                yPoints = VIEWPORT_ORIGINAL.height - (yCanvas / GLOBAL_SCALE);
            }

            // 2. Convertir les points en millimètres
            const xMm = (xPoints * MM_PER_POINT) - 2; // 3 => marge d'erreur
            const yMm = yPoints * MM_PER_POINT;

            return {
                x: +xMm.toFixed(2),
                y: +yMm.toFixed(2),
            };
        }

        demarrer.addEventListener('click', function() {
            canvas.addEventListener('mousemove', updateCords);
            canvas.classList.add('cursor-crosshair');
        });

        function updateCords(e) {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const xCanvasAdjusted = x * scaleX;
            const yCanvasAdjusted = y * scaleY;
            console.log(xCanvasAdjusted, yCanvasAdjusted);
            const cordos = convertToMm(xCanvasAdjusted, yCanvasAdjusted);
            cordX.value = cordos.x;
            cordY.value = cordos.y;

            cordX.dispatchEvent(new Event('input', {
                bubbles: true
            }));
            cordY.dispatchEvent(new Event('input', {
                bubbles: true
            }));
        }

        canvas.addEventListener('click', function() {
            canvas.classList.remove('cursor-crosshair');
            canvas.removeEventListener('mousemove', updateCords);
        })
    </script>
    <script>
        function formEditor(config) {
            return {
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
            }
        }
    </script>
@endpush

@section('content')
    <div class="w-full overflow-y-scroll">
        <div x-data="formEditor({
            elements: {{ Illuminate\Support\Js::from($document->config['elements'] ?? []) }},
            documentId: {{ $document->id }},
            totalPages: {{ $totalPages }},
            fonts: {{ Illuminate\Support\Js::from($fonts ?? []) }}
        })" x-cloak class="flex h-screen bg-red-100">

            <!-- Colonne de gauche : Liste des éléments -->
            <aside class="h-full flex-1 flex flex-col bg-white">
                <div class="p-4">
                    <button @click="addElement()"
                        class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded cursor-pointer">
                        Ajouter un Élément
                    </button>
                </div>
                <div class="flex-grow overflow-y-auto">
                    <template x-for="element in elements" :key="element.id">
                        <div @click="selectElement(element.id)"
                            class="shadow-sm p-3  mt-1 cursor-pointer hover:bg-gray-50 flex justify-between items-center"
                            :class="{ 'bg-blue-100 hover:bg-blue-100': selectedId === element.id }">
                            <div>
                                <!-- Affiche le label ou la valeur si pas de label -->
                                <p class="font-semibold" x-text="element.label || element.value"></p>
                                <p class="text-sm text-gray-600">
                                    Type: <span x-text="element.type"></span>,
                                    Page: <span x-text="element.page"></span>
                                </p>
                            </div>
                            <button @click.stop="removeElement(element.id)"
                                class="text-red-500 hover:text-red-700 text-xl">&times;</button>
                        </div>
                    </template>
                </div>
            </aside>

            <!-- Colonne du centre : Prévisualisation PDF -->
            <main class="flex-3 relative h-full flex flex-col bg-gray-200">
                <canvas id="the-canvas" class="border-2 border-blue-400 shadow-lg"></canvas>
                <div class="flex items-center justify-center gap-3 mt-4">
                    <button type="button"
                        class="border px-4 py-2 cursor-pointer rounded bg-gray-100 hover:bg-gray-200 font-medium"
                        id="prevPage">← Précédente</button>
                    <span class="text-xl font-bold mx-2">Page <span id="currentPage">1</span></span>
                    <button class="border px-4 py-2 rounded cursor-pointer bg-gray-100 hover:bg-gray-200 font-medium"
                        type="button" id="nextPage">Suivante →</button>
                </div>
            </main>
            <!-- Colonne de droite : Éditeur de propriétés -->
            <aside class="h-full flex-1 flex flex-col  bg-white">
                <div class="p-4 overflow-y-auto space-y-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-700">
                        Propriétés
                    </h2>

                    <div x-show="!selectedId" class="text-sm text-gray-400">
                        Sélectionnez un élément à gauche pour l’éditer.
                    </div>

                    <div x-show="selectedId" x-if="selectedElement()" class="space-y-5">

                        <!-- Label + Type -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Label</label>
                                <input type="text" x-model="selectedElement().label"
                                    class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Page</label>
                                <select x-model.number="selectedElement().page"
                                    class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <template x-for="i in totalPages" :key="i">
                                        <option :value="i" x-text="i"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Page + Valeur -->
                        <div class="grid grid-cols-2 gap-3 justify-between">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Type</label>
                                <select x-model="selectedElement().type"
                                    @change="if(selectedElement().type === 'checkbox' && !selectedElement().options) selectedElement().options = []"
                                    class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="text">Texte</option>
                                    <option value="tag">Tag</option>
                                    <option value="checkbox">Checkbox</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Valeur</label>
                                <input type="text" x-model.debounce.200ms="selectedElement().value"
                                    :placeholder="selectedElement().type === 'checkbox' ? '@{{ user.gender }}' :
                                        'Texte ou @{{ tag }}'"
                                    class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div x-show="selectedElement().type == 'tag' || selectedElement().type == 'checkbox' ">
                                <label class="block text-xs text-gray-500 mb-1">Valeur de test</label>
                                <input type="text" x-model.debounce.200ms="selectedElement().valueTest"
                                    class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Coordonnées -->
                        <div class="grid grid-cols-3 gap-3 items-end">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">X (mm)</label>
                                <input type="number" step="0.1" id="cord-x" x-model.number="selectedElement().x"
                                    class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Y (mm)</label>
                                <input type="number" id="cord-y" step="0.1" x-model.number="selectedElement().y"
                                    class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <button id="demarrer" type="button"
                                class="h-9 w-9 flex items-center justify-center text-sm border cursor-pointer z-20 rounded-[100%] sm:text-sm">
                                <img src="{{ asset('target.png') }}" alt="target" width="30" class="z-10">
                            </button>
                        </div>

                        <!-- Style -->
                        <div class="pt-4 border-t space-y-3">
                            <h3 class="text-xs font-semibold uppercase text-gray-600"
                                x-text="selectedElement().type === 'checkbox' ? 'Style du marqueur' : 'Style du texte'">
                            </h3>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Police</label>
                                    <select x-model.number="selectedElement().font_family"
                                        class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <template x-for="f in fonts" :key="f">
                                            <option :value="f" x-text="f"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Taille</label>
                                    <input type="number" x-model.number="selectedElement().font_size"
                                        class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Poids</label>
                                    <select x-model="selectedElement().font_weight"
                                        class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="normal">Normal</option>
                                        <option value="bold">Gras</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Style</label>
                                    <select x-model="selectedElement().font_style"
                                        class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="normal">Normal</option>
                                        <option value="italic">Italique</option>
                                    </select>
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-xs text-gray-500 mb-1">Couleur</label>
                                    <input type="color" :value="selectedElement().hexColor" @input="updateColor($event)"
                                        class="">
                                </div>
                            </div>
                        </div>

                        <!-- Checkbox options -->
                        <div x-show="selectedElement().type === 'checkbox'" class="pt-4 border-t space-y-3">
                            <h3 class="text-xs font-semibold uppercase text-gray-600">
                                Options Checkbox
                            </h3>

                            <button @click="addCheckboxOption()" type="button"
                                class="w-full h-9 text-sm border rounded hover:bg-blue-100">
                                + Ajouter une option
                            </button>

                            <template x-for="(option, index) in selectedElement().options" :key="index">
                                <div class="p-3 shadow-md border rounded space-y-2">
                                    <div class="flex justify-between items-center text-xs font-medium">
                                        <span x-text="`Option ${index + 1}`"></span>
                                        <button @click="removeCheckboxOption(index)"
                                            class="text-red-500 hover:text-red-700">&times;</button>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <input x-model="option.label" placeholder="Label"
                                            class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <input x-model="option.value" placeholder="Valeur"
                                            class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <input type="number" step="0.1" x-model.number="option.x" placeholder="X"
                                            class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <input type="number" step="0.1" x-model.number="option.y" placeholder="Y"
                                            class="block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 border-t">
                    <div class=" flex items-center gap-1">
                        <button @click="save()" :disabled="isSaving"
                            class="inline-flex justify-center rounded-md border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <span x-show="!isSaving">Sauvegarder</span>
                            <span x-show="isSaving">Sauvegarde...</span>
                        </button>
                        <a target="_blank" href="{{ route('documents.download', $document->id) }}"
                            class="inline-flex justify-center rounded-md border border-transparent bg-emerald-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Tester
                        </a>
                    </div>
                    <i class="mt-2 text-red-700 block font-normal">sauvegarder avant le test!</i>
                </div>
            </aside>
        </div>
    </div>
@endsection
