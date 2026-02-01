<x-app-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Éditer la CNSS #{{ $cnss->id }}</h1>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('cnss.update', $cnss) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="patient" class="block text-gray-700 text-sm font-bold mb-2">Patient:</label>
                        <input type="text" name="patient" id="patient" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('patient', $cnss->patient) }}" required>
                    </div>
                    <div>
                        <label for="cin" class="block text-gray-700 text-sm font-bold mb-2">CIN:</label>
                        <input type="text" name="cin" id="cin" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('cin', $cnss->cin) }}" required>
                    </div>
                    <div>
                        <label for="adresse" class="block text-gray-700 text-sm font-bold mb-2">Adresse:</label>
                        <input type="text" name="adresse" id="adresse" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('adresse', $cnss->adresse) }}" required>
                    </div>
                    <div>
                        <label for="date_naissance" class="block text-gray-700 text-sm font-bold mb-2">Date Naissance:</label>
                        <input type="date" name="date_naissance" id="date_naissance" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('date_naissance', $cnss->date_naissance) }}" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Sexe:</label>
                        <label for="homme" class=" text-gray-700 text-sm font-bold mb-2">H</label>
                        <input type="radio" id="homme" name="sexe" class="mr-2" value="H" {{ old('sexe', $cnss->sexe) == 'H' ? 'checked' : '' }} />
                        <label for="femme" class=" text-gray-700 text-sm font-bold mb-2" >F</label>
                        <input type="radio" id="femme" name="sexe" value="F" {{ old('sexe', $cnss->sexe) == 'F' ? 'checked' : '' }}/>
                    </div>
                    <div>
                        <label for="parente" class="block text-gray-700 text-sm font-bold mb-2">Parente:</label>
                        <select name="parente" id="parente" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                            <option value="">-- Sélectionner --</option>
                            <option value="Assuré" {{ old('parente', $cnss->parente) == 'Assuré' ? 'selected' : '' }}>Assuré</option>
                            <option value="Enfant" {{ old('parente', $cnss->parente) == 'Enfant' ? 'selected' : '' }}>Enfant</option>
                            <option value="Conjoint" {{ old('parente', $cnss->parente) == 'Conjoint' ? 'selected' : '' }}>Conjoint</option>
                        </select>
                    </div>
                    <div>
                        <label for="service_hospitalisation" class="block text-gray-700 text-sm font-bold mb-2">Service Hospitalisation:</label>
                        <input type="text" name="service_hospitalisation" id="service_hospitalisation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('service_hospitalisation', $cnss->service_hospitalisation) }}" required>
                    </div>
                    <div>
                        <label for="inp" class="block text-gray-700 text-sm font-bold mb-2">INP:</label>
                        <input type="text" name="inp" id="inp" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('inp', $cnss->inp) }}" required>
                    </div>
                    <div>
                        <label for="nature_hospitalisation" class="block text-gray-700 text-sm font-bold mb-2">Nature Hospitalisation:</label>
                        <input type="text" name="nature_hospitalisation" id="nature_hospitalisation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('nature_hospitalisation', $cnss->nature_hospitalisation) }}" required>
                    </div>
                    <div>
                        <label for="motif_hospitalisation" class="block text-gray-700 text-sm font-bold mb-2">Motif Hospitalisation:</label>
                        <input type="text" name="motif_hospitalisation" id="motif_hospitalisation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('motif_hospitalisation', $cnss->motif_hospitalisation) }}" required>
                    </div>
                    <div>
                        <label for="date_previsible_hospitalisation" class="block text-gray-700 text-sm font-bold mb-2">Date Previsible Hospitalisation:</label>
                        <input type="date" name="date_previsible_hospitalisation" id="date_previsible_hospitalisation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('date_previsible_hospitalisation', $cnss->date_previsible_hospitalisation) }}" required>
                    </div>
                    <div>
                        <label for="date_en_urgence_le" class="block text-gray-700 text-sm font-bold mb-2">Date en Urgence le:</label>
                        <input type="date" name="date_en_urgence_le" id="date_en_urgence_le" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('date_en_urgence_le', $cnss->date_en_urgence_le) }}" required>
                    </div>
                    <div>
                        <label for="nom_etablissement" class="block text-gray-700 text-sm font-bold mb-2">Nom Etablissement:</label>
                        <input type="text" name="nom_etablissement" id="nom_etablissement" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('nom_etablissement', $cnss->nom_etablissement) }}" required>
                    </div>
                    <div>
                        <label for="code_etablissement" class="block text-gray-700 text-sm font-bold mb-2">Code Etablissement:</label>
                        <input type="text" name="code_etablissement" id="code_etablissement" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('code_etablissement', $cnss->code_etablissement) }}" required>
                    </div>
                    <div>
                        <label for="tel" class="block text-gray-700 text-sm font-bold mb-2">Tel:</label>
                        <input type="text" name="tel" id="tel" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('tel', $cnss->tel) }}" required>
                    </div>
                    <div>
                        <label for="total_estime" class="block text-gray-700 text-sm font-bold mb-2">Total Estime:</label>
                        <input type="number" step="0.01" name="total_estime" id="total_estime" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('total_estime', $cnss->total_estime) }}" required>
                    </div>
                    <div>
                        <label for="total" class="block text-gray-700 text-sm font-bold mb-2">Total:</label>
                        <input type="number" step="0.01" name="total" id="total" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="{{ old('total', $cnss->total) }}" required>
                    </div>
                    <div>
                        <label for="template_id" class="block text-gray-700 text-sm font-bold mb-2">Template Document:</label>
                        <select name="template_id" id="template_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                            <option value="">-- Sélectionner un template --</option>
                            @foreach ($documents as $document)
                                <option value="{{ $document->id }}" {{ old('template_id', $cnss->template_id) == $document->id ? 'selected' : '' }}>
                                    {{ $document->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Mettre à jour CNSS
                    </button>
                    <a href="{{ route('cnss.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>