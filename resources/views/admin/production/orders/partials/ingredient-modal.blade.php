{{-- Ingredient Preview Modal --}}
<div id="ingredientPreviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Ingredient Requirements Preview</h3>
                    <button type="button" id="closeIngredientPreviewModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
                <div id="ingredientPreviewContent" class="p-6">
                    {{-- Content will be loaded dynamically --}}
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancelIngredientPreview"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" id="proceedWithIngredients"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Proceed with These Ingredients
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
