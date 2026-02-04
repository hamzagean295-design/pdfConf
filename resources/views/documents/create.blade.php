@extends('layouts.app')

@section('content')
    <div  class="max-w-5xl mx-auto py-12">
        <h1 class="mb-4 text-2xl font-bold">Nouveau modèle</h1>
        <form action="{{ route('documents.store') }}" enctype="multipart/form-data" method="POST">
            @csrf
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1">
                    <label class="block mb-2.5 text-sm font-medium text-heading" for="name">Nom</label>
                    <input type="text" id="name" name="name" placeholder="Ex: Facture" class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('name')
                        <div class="text-red-500">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="flex-1">
                    <label class="block mb-2.5 text-sm font-medium text-heading" for="file_input">Charger un fichier pdf</label>
                    <input class="cursor-pointer bg-neutral-secondary-medium border border-default-medium  text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full shadow-xs placeholder:text-body shadow-sm" aria-describedby="file_input_help" id="file_input" name="document" type="file">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" id="file_input_help">PDF</p>
                    @error('document')
                        <div class="text-red-500">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
            <button type="submit" class="bg-black text-white rounded-sm p-2 mt-2">Sauvegarder</button>
        </form>
    </div>
@endsection