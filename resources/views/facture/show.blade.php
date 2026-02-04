@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Détails de la Facture #{{ $facture->id }}</h1>

        <div class="bg-white shadow-md rounded-lg p-6 mb-4">
            <div class="mb-4">
                <p class="block text-gray-700 text-sm font-bold mb-2">Nom du Client:</p>
                <p class="text-gray-900 text-lg">{{ $facture->customer_name }}</p>
            </div>

            <div class="mb-4">
                <p class="block text-gray-700 text-sm font-bold mb-2">Montant:</p>
                <p class="text-gray-900 text-lg">{{ number_format($facture->montant, 2) }} €</p>
            </div>

            <div class="mb-4">
                <p class="block text-gray-700 text-sm font-bold mb-2">Date Facture:</p>
                <p class="text-gray-900 text-lg">{{ $facture->date_facture }}</p>
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="{{ route('factures.edit', $facture) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Éditer
                </a>
                <form action="{{ route('factures.destroy', $facture) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?')">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        @if ($facture->document_path)
            <div class="mt-6">
                <a href="{{ $facture->generatedPdfUrl() }}" target="_blank" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    voir le pdf
                </a>
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('factures.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Retour à la liste
            </a>
        </div>
    </div>
@endsection