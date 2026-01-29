<x-app-layout>
    <div  class="max-w-5xl mx-auto py-12">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Liste de modèles</h1>
            <a class="text-blue-500 hover:underline" href="{{ route('documents.create') }}">Charger un nouveau modèle</a>
        </div>
        <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
            <table class="w-full text-sm text-left rtl:text-right text-body">
                <thead class="bg-neutral-secondary-soft border-b border-default">
                    <th scope="col" class="px-6 py-3 font-medium"> Nom <i class="block text-xs font-normal">(double click pour changer)</i></th>
                    <th scope="col" class="px-6 py-3 font-medium"> Actions</th>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                    <tr class="odd:bg-neutral-primary even:bg-neutral-secondary-soft border-b border-default">
                            <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                @livewire('templat-name', ['document' => $document])
                            </th>
                            <td class="px-6 py-4">
                                <a class="font-medium text-blue-500 text-fg-brand hover:underline"  target="_blank" href="{{ route('documents.show', ['document' => $document]) }}">Voir</a>
                            </td>
                            <td class="px-6 py-4">
                                <a class="font-medium text-fg-brand hover:underline" href="{{ route('documents.edit-simple', ['document' => $document]) }}">Configurer</a>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('documents.destroy', ['document' => $document]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button  onclick="return confirm('{{ __('Voulez-vous vraiment supprimer cet élément ?') }}')" type="submit" class="font-medium text-red-600 text-fg-brand hover:underline">supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td>aucun modèle</td></tr>
                    @endforelse
                </tbody>
            </table>
    </div>
</x-app-layout>
