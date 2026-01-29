<x-app-layout>
    @push('scripts')
        <!-- CDNs -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.107/pdf.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Logique Applicative -->
        <script>
            function pdfEditor(config) {
                return {
                    // --- STATE ---
                    elements: [],
                    selectedId: null,
                    pxPerMm: 0,
                    pdfUrl: config.pdfUrl,
                    documentId: config.documentId,
                    isSaving: false,
                    totalPages: 0,
                    currentPage: 1,

                    // --- INIT ---
                    init() {
                        this.elements = config.elements.map((el, index) => ({
                            ...el,
                            id: Date.now() + index,
                            page: el.page || 1 // Assurer que chaque élément a une page
                        }));
                        this.renderPdf(this.currentPage);
                        this.initDraggable();
                        window.addEventListener('resize', this.debounce(() => this.renderPdf(this.currentPage), 250));
                    },

                    // --- CORE METHODS ---
                    async renderPdf(pageNumber) {
                        if (typeof pdfjsLib === 'undefined') {
                            setTimeout(() => this.renderPdf(pageNumber), 200);
                            return;
                        }
                        pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.107/pdf.worker.min.js`;

                        const pdf = await pdfjsLib.getDocument(this.pdfUrl).promise;
                        this.totalPages = pdf.numPages;
                        this.currentPage = Math.max(1, Math.min(pageNumber, this.totalPages));

                        const page = await pdf.getPage(this.currentPage);
                        const viewport = page.getViewport({ scale: 1.5 });
                        const canvas = document.getElementById('pdf-canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        await page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise;
                        this.pxPerMm = canvas.clientWidth / 210;
                    },

                    initDraggable() {
                        interact('.draggable').draggable({
                            listeners: {
                                move: (event) => {
                                    const element = this.selectedElement();
                                    if (element && this.pxPerMm > 0) {
                                        element.x += event.dx / this.pxPerMm;
                                        element.y += event.dy / this.pxPerMm;
                                    }
                                }
                            },
                            modifiers: [interact.modifiers.restrictRect({ restriction: 'parent' })],
                        });
                    },

                    async save() { /* ... (inchangé) ... */ },

                    // --- GETTERS & HELPERS ---
                    selectedElement() {
                        if (!this.selectedId) return null;
                        return this.elements.find(el => el.id === this.selectedId);
                    },
                    getStyle(element) { /* ... (inchangé) ... */ },
                    addElement(type) {
                        const newElement = {
                            id: Date.now(), type: type, label: `Nouveau (${type})`,
                            value: type === 'tag' ? '@{{ nouveau.tag }}' : 'Texte',
                            x: 20, y: 20, font_family: 'Helvetica', font_style: '', font_size: 12, color: [0, 0, 0],
                            page: this.currentPage
                        };
                        this.elements.push(newElement);
                        this.selectElement(newElement.id);
                    },
                    selectElement(id) { this.selectedId = id; },
                    debounce(func, wait) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => func.apply(this, a), wait); }; }
                }
            }
        </script>
    @endpush

    <div x-data="pdfEditor({
            elements: {{ Illuminate\Support\Js::from($document->config['elements'] ?? []) }},
            pdfUrl: '{{ $pdfUrl }}',
            documentId: {{ $document->id }}
        })" x-cloak class="flex h-screen">

        <!-- Zone de prévisualisation -->
        <main class="w-3/4 h-full bg-gray-800 flex flex-col items-center p-4">
            <div id="pdf-container" class="relative inline-block bg-gray-200 shadow-lg mb-4">
                <canvas id="pdf-canvas"></canvas>
                <template x-for="element in elements.filter(el => el.page == currentPage)" :key="element.id">
                    <div :data-id="element.id" @click="selectElement(element.id)" :style="getStyle(element)"
                        class="draggable absolute cursor-move group p-1 z-50"
                        :class="{ 'border-2 border-blue-500 bg-blue-500 bg-opacity-20': selectedId === element.id }">
                        <span class="whitespace-nowrap" x-text="element.value"></span>
                    </div>
                </template>
            </div>
            <!-- Pagination -->
            <div class="flex items-center space-x-4 text-white">
                <button @click="renderPdf(currentPage - 1)" :disabled="currentPage <= 1" class="px-4 py-2 bg-gray-600 rounded disabled:opacity-50">Précédent</button>
                <span>Page <span x-text="currentPage"></span> / <span x-text="totalPages"></span></span>
                <button @click="renderPdf(currentPage + 1)" :disabled="currentPage >= totalPages" class="px-4 py-2 bg-gray-600 rounded disabled:opacity-50">Suivant</button>
            </div>
        </main>

        <!-- Barre latérale d'édition -->
        <aside class="w-1/4 h-full bg-white border-l p-4 overflow-y-auto flex flex-col">
            <div class="flex-grow">
                <h2 class="text-lg font-bold mb-4">Éditeur</h2>
                <button @click="addElement('tag')" class="w-full mb-2 bg-blue-500 text-white font-bold py-2 px-4 rounded">Ajouter Tag</button>
                <button @click="addElement('text')" class="w-full mb-4 bg-green-500 text-white font-bold py-2 px-4 rounded">Ajouter Texte</button>
                <hr>
                <!-- Formulaire de l'élément sélectionné -->
                <div x-show="selectedId" x-transition class="space-y-3 mt-4" x-if="selectedElement()">
                    <h3 class="font-bold text-md">Propriétés de l'élément</h3>
                    <div>
                        <label class="block text-sm font-medium">Type</label>
                        <select x-model="selectedElement().type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="text">Texte</option>
                            <option value="tag">Tag</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Page</label>
                        <select x-model.number="selectedElement().page" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <template x-for="i in Array.from({length: totalPages}, (_, i) => i + 1)" :key="i">
                                <option :value="i" x-text="i"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Valeur</label>
                        <input type="text" x-model.debounce.200ms="selectedElement().value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">X (mm)</label>
                            <input type="number" step="0.1" x-model.number="selectedElement().x" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Y (mm)</label>
                            <input type="number" step="0.1" x-model.number="selectedElement().y" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Police</label>
                        <input type="text" x-model.debounce.200ms="selectedElement().font_family" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Taille Police</label>
                        <input type="number" x-model.number="selectedElement().font_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                     <div>
                        <label class="block text-sm font-medium">Couleur (RGB)</label>
                        <input type="text" x-model.debounce.200ms="selectedElement().color" placeholder="ex: 0,0,0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button @click="save()" :disabled="isSaving" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded disabled:bg-gray-400">
                    <span x-show="!isSaving">Sauvegarder</span>
                    <span x-show="isSaving">Sauvegarde...</span>
                </button>
            </div>
        </aside>
    </div>
</x-app-layout>