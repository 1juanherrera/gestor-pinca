import { Calculator, Loader2, FileDown } from 'lucide-react';
import { FaSyncAlt, FaCheckSquare  } from 'react-icons/fa';

export const CostCalculator = ({ 
    productDetail,
    selectedProductData,
    loadingDetail,
    compact = false,
    handleRecalcular,
    setNuevoVolumen,
    recalculatedData,
    isRecalculating
}) => {
    

    if (!selectedProductData) {
        return (
            <div className="bg-white rounded-lg shadow-sm p-4 text-center">
                <div className="text-gray-400 mb-3">
                    <Calculator size={compact ? 32 : 48} className="mx-auto" />
                </div>
                <h3 className={`${compact ? 'text-base' : 'text-lg'} font-medium text-gray-900 mb-2`}>
                    Calculadora de Costos
                </h3>
                <p className="text-sm text-gray-500">
                    Selecciona un producto para calcular costos
                </p>
            </div>
        );
    }
    
    const isProcessing = loadingDetail || isRecalculating;

    return (
        <div className="bg-white rounded-lg shadow-sm overflow-hidden">
            {/* Header */}
            <div className="bg-linear-to-r from-purple-500 to-purple-600 text-white px-4 py-3">
                <div className="flex items-center justify-between">
                    <div>
                        <h3 className={`${compact ? 'text-base' : 'text-lg'} font-semibold flex items-center gap-2`}>
                            <Calculator size={compact ? 16 : 20} />
                            Calculadora de Costos
                        </h3>
                        <p className="text-purple-100 text-xs">
                            {productDetail?.item?.nombre || selectedProductData.nombre} - {productDetail?.item?.codigo || selectedProductData.codigo}
                        </p>
                    </div>
                </div>
            </div>

            {/* Form */}
            <div className="p-4">
                <div className="grid grid-cols-1 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Nuevo Volumen
                        </label>
                        <div className="relative">
                            <div className="flex gap-2">
                            <input
                                type="number"
                                onChange={(e) => setNuevoVolumen(e.target.value)}
                                placeholder={productDetail?.item?.volumen_actual || '0'}
                                className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent pr-10" // pr-10 para el spinner
                                min="0.01"
                                step="0.01"
                                disabled={isProcessing}
                            />
                            <button 
                            onClick={handleRecalcular}
                            disabled={isRecalculating}
                            className="bg-purple-600 text-white px-3 py-2 rounded-lg">
                            {isRecalculating ? (
                                <>
                                    <FaSyncAlt className="animate-spin" />
                                </>
                                ) : (
                                <>
                                    <FaSyncAlt />
                                </>
                            )}
                            </button>
                            </div>
                            {isProcessing && (
                                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <Loader2 className="animate-spin text-purple-600" size={16} />
                                </div>
                            )}
                        </div>
                        {recalculatedData && (
                            <div className="mt-3 p-2 bg-green-50 border border-green-100 flex justify-center items-center rounded-md text-center font-semibold">
                                <FaCheckSquare className="text-green-700" />
                                <p className="text-sm ml-1 text-green-700">
                                 Costo total: {recalculatedData?.recalculados?.total_costo_materia_prima}
                                </p>
                            </div>
                        )}
                    </div>
                    
                    {/* Mensaje de cálculo automático */}
                    <div className={`flex justify-center items-center py-2 bg-purple-50 rounded-lg ${!recalculatedData ? '' : 'hidden'}`}>
                        <p className="text-xs text-purple-700 font-medium">
                            Escribe un valor y el cálculo se actualizará automáticamente.
                        </p>
                    </div>
                </div>
            </div>

            {/* Resultados */}
            { recalculatedData && productDetail && (
                <div className=" bg-gray-50 p-4">
                    <div className="flex items-center justify-between mb-3 border-b pb-2">
                        <h4 className="text-sm font-semibold text-gray-800">
                            Resultados
                        </h4>
                        <div className="flex gap-2">
                            <button
                                disabled={isProcessing}
                                className="flex items-center gap-1 px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-800 disabled:opacity-50 shadow-md"
                            >
                                <FileDown size={18} />
                                Exportar a EXCEL
                            </button>
                            <button
                                disabled={isProcessing}
                                className="flex items-center gap-1 px-3 py-1.5 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-800 disabled:opacity-50 shadow-md"
                            >
                                Preparar
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        {/* Costos Originales */}
                        <div className="bg-white rounded-lg border-2 border-gray-200 p-3">
                            <h5 className="font-semibold text-gray-700 mb-2 text-sm">Original - Vol {productDetail.item?.volumen_base || '0'}</h5>
                            <div className="space-y-1 text-xs">
                                <div className="flex justify-between">
                                    <span>Total Costos:</span>
                                    <span className="font-medium">
                                        {productDetail?.costos.total_costo_materia_prima || 0}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Cantidad</span>
                                    <span className="font-medium">
                                        {productDetail?.costos.total_cantidad_materia_prima || 0}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Venta C/U:</span>
                                    <span className="font-medium">
                                        {productDetail?.costos.precio_venta || 0}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Costos Calculados (Nuevo) */}
                        <div className="bg-white rounded-lg p-3 border-2 border-green-200 relative">
                            {isRecalculating && (
                                <div className="absolute inset-0 bg-white bg-opacity-70 flex flex-col items-center justify-center rounded-lg z-10">
                                    <Loader2 className="animate-spin text-purple-600 mb-2" size={24} />
                                    <p className="text-sm font-semibold text-purple-700">Recalculando...</p>
                                </div>
                            )}

                            <h5 className="font-semibold text-green-700 mb-2 text-sm">
                                Nuevo - Vol {recalculatedData?.item?.volumen_nuevo || '0'}
                            </h5>
                            <div className="space-y-1 text-xs">
                                <div className="flex justify-between">
                                    <span>Total Costos:</span>
                                    <span className="font-medium text-green-600">
                                        {recalculatedData?.recalculados?.total_costo_materia_prima || 0}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Cantidad</span>
                                    <span className="font-medium text-green-600">
                                        {recalculatedData?.recalculados?.total_cantidad_materia_prima || 0}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Venta C/U:</span>
                                    <span className="font-medium text-green-600">
                                        {recalculatedData?.recalculados?.precio_venta || 0} 
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Información adicional (factor y MP Total Original) */}
                    <div className="mt-4 p-2 bg-purple-100 rounded-lg flex justify-between text-xs font-semibold text-purple-800">
                        <p>
                            Factor: x{recalculatedData?.item?.factor_volumen}
                        </p>
                        <p>
                            MP Total Original: {productDetail.costos_originales?.costo_total_materias_primas || 0}
                        </p>
                    </div>

                </div>
            )}
        </div>
    );
};
