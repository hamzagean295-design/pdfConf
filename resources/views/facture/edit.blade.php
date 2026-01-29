<x-app-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Éditer la Facture #{{ $facture->id }}</h1>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('facture.update', $facture) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="customer_name" class="block text-gray-700 text-sm font-bold mb-2">Nom du Client:</label>
                    <input type="text" name="customer_name" id="customer_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('customer_name') border-red-500 @enderror" value="{{ old('customer_name', $facture->customer_name) }}" required>
                    @error('customer_name')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="montant" class="block text-gray-700 text-sm font-bold mb-2">Montant:</label>
                    <input type="number" step="0.01" name="montant" id="montant" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('montant') border-red-500 @enderror" value="{{ old('montant', $facture->montant) }}" required>
                    @error('montant')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="date_facture" class="block text-gray-700 text-sm font-bold mb-2">Date Facture:</label>
                    <input type="date" name="date_facture" id="date_facture" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('date_facture') border-red-500 @enderror" value="{{ old('date_facture', $facture->date_facture) }}" required>
                    @error('date_facture')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="template_id" class="block text-gray-700 text-sm font-bold mb-2">Template Document:</label>
                    <select name="template_id" id="template_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('template_id') border-red-500 @enderror">
                        <option value="">-- Sélectionner un template --</option>
                        @foreach ($documents as $document)
                            <option value="{{ $document->id }}" {{ old('template_id', $facture->template_id) == $document->id ? 'selected' : '' }}>
                                {{ $document->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('template_id')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Mettre à jour Facture
                    </button>
                    <a href="{{ route('factures.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
