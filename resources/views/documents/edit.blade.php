@extends('layouts.app')
@push('scripts')
    <script type="module" src="{{ asset('pdf-viewer.js') }}"></script>
@endpush

@section('content')
    <div id="pdf-viewer-data" data-pdf-url="{{ $pdfUrl }}" data-total-pages="{{ $totalPages }}"
        class="w-full overflow-y-scroll">
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

                    <div x-show="selectedId" x-show="selectedElement()" class="space-y-5">

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
