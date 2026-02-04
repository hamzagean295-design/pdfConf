@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="pb-5 border-b border-gray-200 mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">Nouveau modèle</h1>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('documents.store') }}" enctype="multipart/form-data" method="POST">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                        <div class="mt-1">
                            <input type="text" id="name" name="name" placeholder="Ex: Facture"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="file_input" class="block text-sm font-medium text-gray-700">Charger un fichier pdf</label>
                        <div class="mt-1">
                            <input type="file" id="file_input" name="document"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600">
                        </div>
                        <p class="mt-1 text-sm text-gray-500" id="file_input_help">PDF (MAX. 10MB)</p>
                        @error('document')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection