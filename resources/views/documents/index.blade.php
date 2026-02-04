@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="pb-5 border-b border-gray-200 sm:flex sm:items-center sm:justify-between">
            <h1 class="text-3xl font-extrabold text-gray-900">Liste de modèles</h1>
            <div class="mt-3 sm:ml-4 sm:mt-0">
                <a href="{{ route('documents.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Charger un nouveau modèle
                </a>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden mt-8">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nom
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($documents as $document)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $document->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a class="text-indigo-600 hover:text-indigo-900 mr-4" target="_blank"
                                    href="{{ route('documents.show', ['document' => $document]) }}">Voir</a>
                                <a class="text-blue-600 hover:text-blue-900 mr-4"
                                    href="{{ route('documents.edit', ['document' => $document]) }}">Configurer</a>
                                <form action="{{ route('documents.destroy', ['document' => $document]) }}" method="POST"
                                    class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        onclick="return confirm('{{ __('Voulez-vous vraiment supprimer cet élément ?') }}')"
                                        type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">Aucun modèle trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection