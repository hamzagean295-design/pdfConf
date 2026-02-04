@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Détails de la CNSS #{{ $cnss->id }}</h1>

        <div class="bg-white shadow-md rounded-lg p-6 mb-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Patient:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->patient }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">CIN:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->cin }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Adresse:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->adresse }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Date Naissance:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->date_naissance }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Sexe:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->sexe }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Parente:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->parente }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Service Hospitalisation:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->service_hospitalisation }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">INP:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->inp }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Nature Hospitalisation:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->nature_hospitalisation }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Motif Hospitalisation:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->motif_hospitalisation }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Date Previsible Hospitalisation:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->date_previsible_hospitalisation }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Date en Urgence le:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->date_en_urgence_le }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Nom Etablissement:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->nom_etablissement }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Code Etablissement:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->code_etablissement }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Tel:</p>
                    <p class="text-gray-900 text-lg">{{ $cnss->tel }}</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Total Estime:</p>
                    <p class="text-gray-900 text-lg">{{ number_format($cnss->total_estime, 2) }} €</p>
                </div>
                <div>
                    <p class="block text-gray-700 text-sm font-bold mb-2">Total:</p>
                    <p class="text-gray-900 text-lg">{{ number_format($cnss->total, 2) }} €</p>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="{{ route('cnss.edit', $cnss) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Éditer
                </a>
                <form action="{{ route('cnss.destroy', $cnss) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette CNSS ?')">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        @if ($cnss->document_path)
            <div class="mt-6">
                <a href="{{ $cnss->generatedPdfUrl() }}" target="_blank" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Voir le PDF
                </a>
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('cnss.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Retour à la liste
            </a>
        </div>
    </div>
@endsection
