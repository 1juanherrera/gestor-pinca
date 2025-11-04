import React, { useState, useEffect } from 'react';
import { Calculator, Loader2, FileDown } from 'lucide-react'; // Íconos reemplazados por lucide-react

// Función de utilidad para formatear la moneda consistentemente (simulando formatoPesoColombiano)
const formatCOP = (amount) => {
    const value = parseFloat(amount) || 0;
    return value.toLocaleString('es-CO', { 
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
};

export const CostCalculator = ({ 
    productDetail,
    selectedProductData,
    loadingDetail,
    compact = false,
    // NUEVAS PROPS PARA EL RECÁLCULO
    recalculateVolume = () => {}, // <--- CORRECCIÓN: Se añade un valor por defecto para evitar el TypeError
    isRecalculating 
}) => {
    
    // Estado local para el valor del input, para que el componente sea responsivo
    const [volumeInput, setVolumeInput] = useState('');
    const [error, setError] = useState('');

    // Sincronizar volumeInput: cuando el producto cambia o se carga un nuevo detalle/cálculo
    useEffect(() => {
        if (productDetail && productDetail.volumenes) {
            // Usar volumen_nuevo si está disponible, o el volumen actual del ítem
            const currentVolume = productDetail.volumenes.volumen_nuevo || productDetail.item.volumen_actual;
            setVolumeInput(String(currentVolume));
        } else if (selectedProductData) {
             // Si hay producto seleccionado pero no hay detalle (e.g., al inicio)
             setVolumeInput(String(selectedProductData.volumen_actual || '1'));
        } else {
            setVolumeInput('');
        }
        setError('');
    }, [productDetail, selectedProductData]);


    const handleVolumeChange = (e) => {
        const value = e.target.value;
        setVolumeInput(value); // Actualiza la vista inmediatamente
        
        // Validación básica
        const newVolume = parseFloat(value);

        if (value === '') {
            setError(''); // Permitir vaciar
            return;
        }

        if (isNaN(newVolume)) {
            setError('El volumen debe ser un número.');
            return;
        }
        
        if (newVolume <= 0) {
            setError('El volumen debe ser mayor a 0.');
            return;
        }
        
        setError('');
        
        // 🚨 Llamar a la función DEBOUNCED para iniciar el recálculo
        recalculateVolume(newVolume);
    };

    // Mostrar placeholder si no hay producto seleccionado
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
            <div className="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-4 py-3">
                <div className="flex items-center justify-between">
                    <div>
                        <h3 className={`${compact ? 'text-base' : 'text-lg'} font-semibold flex items-center gap-2`}>
                            <Calculator size={compact ? 16 : 20} />
                            Calculadora de Costos
                        </h3>
                        <p className="text-purple-100 text-xs">
                            {productDetail?.item?.nombre || selectedProductData.nombre}
                        </p>
                    </div>
                    <div className="text-right">
                        <div className="text-xs text-purple-100">
                            Vol. Actual: {productDetail?.item?.volumen_actual || 0} L
                        </div>
                        <div className="text-xs text-purple-100">
                            Costo Original: {formatCOP(parseFloat(productDetail?.costos_originales?.costo_total_materias_primas))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Form */}
            <div className="p-4">
                <div className="grid grid-cols-1 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Nuevo Volumen (Cálculo Automático)
                        </label>
                        <div className="relative">
                            <div className="flex gap-2">
                            <input
                                type="number"
                                value={volumeInput}
                                onChange={handleVolumeChange}
                                placeholder={productDetail?.item?.volumen_actual || '1'}
                                className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent pr-10" // pr-10 para el spinner
                                min="0.01"
                                step="0.01"
                                disabled={isProcessing}
                            />
                            <button className="bg-blue-600 text-white px-3 py-2 rounded-lg">Calcular</button>
                            </div>
                            {isProcessing && (
                                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <Loader2 className="animate-spin text-purple-600" size={16} />
                                </div>
                            )}
                        </div>
                        {error && (
                            <p className="mt-1 text-xs text-red-600">{error}</p>
                        )}
                    </div>
                    
                    {/* Mensaje de cálculo automático */}
                    <div className="flex justify-center items-center py-2 bg-purple-50 rounded-lg">
                        <p className="text-xs text-purple-700 font-medium">
                            Escribe un valor y el cálculo se actualizará automáticamente.
                        </p>
                    </div>
                </div>
            </div>

            {/* Resultados */}
            {productDetail && (
                <div className=" bg-gray-50 p-4">
                    <div className="flex items-center justify-between mb-3 border-b pb-2">
                        <h4 className="text-sm font-semibold text-gray-800">
                            Resultados
                        </h4>
                        <button
                            disabled={isProcessing}
                            className="flex items-center gap-1 px-3 py-1.5 text-sm bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50 transition-colors shadow-md"
                        >
                            <FileDown size={18} />
                            Exportar a EXCEL
                        </button>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        {/* Costos Originales */}
                        <div className="bg-white rounded-lg border-2 border-gray-200 p-3">
                            <h5 className="font-semibold text-gray-700 mb-2 text-sm">Original ({productDetail?.item?.volumen_actual || 0} L)</h5>
                            <div className="space-y-1 text-xs">
                                <div className="flex justify-between">
                                    <span>Total MP:</span>
                                    <span className="font-medium">
                                        {formatCOP(productDetail?.costos_originales?.costo_total_materias_primas || 0)}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Costo C/U:</span>
                                    <span className="font-medium">
                                        {formatCOP(productDetail?.costos_originales?.costo_unitario_total || 0)}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Venta C/U:</span>
                                    <span className="font-medium">
                                        {formatCOP(productDetail?.costos_originales?.precio_venta || 0)}
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
                                Nuevo Cálculo ({productDetail.volumenes?.volumen_nuevo || '0'} L)
                            </h5>
                            <div className="space-y-1 text-xs">
                                <div className="flex justify-between">
                                    <span>Factor:</span>
                                    <span className="font-medium text-green-600">{productDetail.volumenes?.factor_volumen || '1.00'}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Total MP:</span>
                                    <span className="font-medium text-green-600">
                                        {formatCOP(productDetail.costos_nuevos?.costo_total_materias_primas || 0)}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Costo C/U:</span>
                                    <span className="font-medium text-green-600">
                                        {formatCOP(productDetail.costos_nuevos?.costo_unitario_total || 0)}
                                    </span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Venta C/U:</span>
                                    <span className="font-medium text-green-600">
                                        {formatCOP(productDetail.costos_nuevos?.precio_venta || 0)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Información adicional (factor y MP Total Original) */}
                    <div className="mt-4 p-2 bg-purple-100 rounded-lg flex justify-between text-xs font-semibold text-purple-800">
                        <p>
                            Factor Venta: **{productDetail.config?.factor_venta || '1.4'}x**
                        </p>
                        <p>
                            MP Total Original: {formatCOP(productDetail.costos_originales?.costo_total_materias_primas || 0)}
                        </p>
                    </div>

                </div>
            )}
        </div>
    );
};
