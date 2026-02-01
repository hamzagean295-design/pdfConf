<x-app-layout>
    @push('scripts')
    <script>
        const rightSide = document.getElementById('right-side');
        let elements = @json($document->config['elements']);
        console.log(elements);
        function displayElement(id) {
            let element =  elements[parseInt(id)];
            // console.log(elements[parseInt(id)])
            const div = document.createElement('div');
            const inputLabel = document.createElement('input');
            inputLabel.type = "text" ;
            inputLabel.value = element.label;
            inputLabel.classList.add= "border p-1 h-10";
            div.appendChild(inputLabel);
            rightSide.appendChild(div);
        }
    </script>
    @endpush
    <div class="grid grid-cols-9 w-full">
        <aside class="col-span-2 bg-yellow-500 p-2">
            <button type="button" class="text-xl font-normal border p-1 cursor-pointer rounded-md text-white   bg-black">Ajouter un Element</button>
            <ul class="flex flex-col justify-center gap-2 mt-1">
                @forelse ($document->config['elements'] as $key => $element)
                    <li id="{{ $key }}" class="border p-1 relative" onclick="displayElement({{ $key }})">
                        <h3 class="font-bold text-xl capitalize">{{ $element['label'] }}</h3>
                        <span>type: {{ $element['type']}}, page: {{ $element['page'] }}</span>
                        <span class="text-red-800 absolute right-[1rem] top-[1rem] text-2xl text-center w-8 bg-white">x</span>
                    </li>
                @empty
                    <i>aucun élement</i>
                @endforelse
            </ul>
        </aside>
        <div class="col-span-5 bg-green-500">
            <canvas id="the-canvas"></canvas>
        </div>
        <aside class="col-span-2 bg-red-500 p-2" id="right-side">
            <h2 class="text-xl font-normal">Propriétés</h2>
            <i>Sélectionnez un élément à gauche pour l'éditer.</i>
        </aside>
    </div>
</x-app-layout>
