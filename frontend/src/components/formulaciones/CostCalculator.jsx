// import React, { useState } from 'react';
// import { FaCalculator, FaSpinner, FaUndo } from 'react-icons/fa';
// import { MdScience } from 'react-icons/md';
// import { RiFileExcel2Line } from "react-icons/ri";
// import { formatoPesoColombiano } from '../../utils/formatters';

// export const CostCalculator = ({ 
//     selectedProductData, 
//     onCalculate, 
//     onSave, 
//     loading,
//     onCalculationChange,
//     compact = false
// }) => {
//     const [newVolume, setNewVolume] = useState('');
//     const [calculation, setCalculation] = useState(null);
//     const [error, setError] = useState('');

//     const handleVolumeChange = (e) => {
//         const value = e.target.value;
//         setNewVolume(value);
//         setError('');
//     };

//     const handleCalculate = async () => {
//         if (!newVolume || parseFloat(newVolume) <= 0) {
//             setError('Por favor ingresa un volumen válido mayor a 0');
//             return;
//         }

//         try {
//             const result = await onCalculate(selectedProductData.id, parseFloat(newVolume));
//             setCalculation(result);
//             setError('');
            
//             if (onCalculationChange) {
//                 onCalculationChange(result);
//             }
//         } catch (err) {
//             setError('Error al calcular: ' + err.message);
//             setCalculation(null);
            
//             if (onCalculationChange) {
//                 onCalculationChange(null);
//             }
//         }
//     };

//     const handleSave = async () => {
//         if (!calculation) return;
        
//         try {
//             await onSave(selectedProductData.id, calculation.costos_nuevos);
//             setCalculation(null);
//             setNewVolume('');
            
//             if (onCalculationChange) {
//                 onCalculationChange(null);
//             }
//         } catch (err) {
//             setError('Error al guardar: ' + err.message);
//         }
//     };

//     const handleReset = () => {
//         setNewVolume('');
//         setCalculation(null);
//         setError('');
        
//         if (onCalculationChange) {
//             onCalculationChange(null);
//         }
//     };

//     if (!selectedProductData) {
//         return (
//             <div className="bg-white rounded-lg shadow-sm p-4 text-center">
//                 <div className="text-gray-400 mb-3">
//                     <FaCalculator size={compact ? 32 : 48} className="mx-auto" />
//                 </div>
//                 <h3 className={`${compact ? 'text-base' : 'text-lg'} font-medium text-gray-900 mb-2`}>
//                     Calculadora de Costos
//                 </h3>
//                 <p className="text-sm text-gray-500">
//                     Selecciona un producto para calcular costos
//                 </p>
//             </div>
//         );
//     }

//     return (
//         <div className="bg-white rounded-lg shadow-sm overflow-hidden">
//             {/* Header */}
//             <div className="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-4 py-3">
//                 <div className="flex items-center justify-between">
//                     <div>
//                         <h3 className={`${compact ? 'text-base' : 'text-lg'} font-semibold flex items-center gap-2`}>
//                             <FaCalculator size={compact ? 16 : 20} />
//                             Calculadora de Costos
//                         </h3>
//                         <p className="text-purple-100 text-xs">
//                             {selectedProductData.nombre}
//                         </p>
//                     </div>
//                     <div className="text-right">
//                         <div className="text-xs text-purple-100">
//                             Vol: {selectedProductData.volumen_costo || 0}
//                         </div>
//                         <div className="text-xs text-purple-100">
//                             $ {selectedProductData.costo_total?.toFixed(2) || '0.00'}
//                         </div>
//                     </div>
//                 </div>
//             </div>

//             {/* Form */}
//             <div className="p-4">
//                 <div className="grid grid-cols-1 gap-4">
//                     <div>
//                         <label className="block text-sm font-medium text-gray-700 mb-1">
//                             Nuevo Volumen
//                         </label>
//                         <div className="relative">
//                             <input
//                                 type="number"
//                                 value={newVolume}
//                                 onChange={handleVolumeChange}
//                                 placeholder="Ingresa volumen"
//                                 className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
//                                 min="0"
//                                 step="0.01"
//                             />
//                         </div>
//                         {error && (
//                             <p className="mt-1 text-xs text-red-600">{error}</p>
//                         )}
//                     </div>

//                     <div className="flex gap-2">
//                         <button
//                             onClick={handleCalculate}
//                             disabled={loading || !newVolume}
//                             className="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-600 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
//                         >
//                             {loading ? (
//                                 <>
//                                     <FaSpinner className="animate-spin" size={12} />
//                                     Calculando...
//                                 </>
//                             ) : (
//                                 <>
//                                     <MdScience size={12} />
//                                     Calcular
//                                 </>
//                             )}
//                         </button>
                        
//                         <button
//                             onClick={handleReset}
//                             className="px-3 py-2 text-sm bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
//                         >
//                             <FaUndo size={12} />
//                         </button>
//                     </div>
//                 </div>
//             </div>

//             {/* Resultados */}
//             {calculation && (
//                 <div className="border-t bg-gray-50 p-4">
//                     <div className="flex items-center justify-between mb-3">
//                         <h4 className="text-sm font-semibold text-gray-800">
//                             Resultados
//                         </h4>
//                         <button
//                             onClick={handleSave}
//                             disabled={loading}
//                             className="flex items-center gap-1 px-3 py-1.5 text-sm bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50 transition-colors"
//                         >
//                             {loading ? (
//                                 <>
//                                     <FaSpinner className="animate-spin" size={10} />
//                                     Guardando...
//                                 </>
//                             ) : (
//                                 <>
//                                     <RiFileExcel2Line size={18} />
//                                     Exportar a EXCEL
//                                 </>
//                             )}
//                         </button>
//                     </div>

//                     <div className="grid grid-cols-2 gap-3">
//                         <div className="bg-white rounded-lg border-2 border-gray-200 p-3">
//                             <h5 className="font-semibold text-gray-700 mb-2 text-sm">Original</h5>
//                             <div className="space-y-1 text-xs">
//                                 <div className="flex justify-between">
//                                     <span>Vol:</span>
//                                     <span className="font-medium">{calculation.volumenes?.volumen_original || 0}</span>
//                                 </div>
//                                 <div className="flex justify-between">
//                                     <span>Total:</span>
//                                     <span className="font-medium">
//                                         ${(calculation.costos_originales?.costo_total || 0).toLocaleString('es-CO', { 
//                                             minimumFractionDigits: 2, 
//                                             maximumFractionDigits: 2 
//                                         })}
//                                     </span>
//                                 </div>
//                                 <div className="flex justify-between">
//                                     <span>Precio:</span>
//                                     <span className="font-medium">
//                                         ${(calculation.costos_originales?.precio_venta || 0).toLocaleString('es-CO', { 
//                                             minimumFractionDigits: 2, 
//                                             maximumFractionDigits: 2 
//                                         })}
//                                     </span>
//                                 </div>
//                             </div>
//                         </div>

//                         <div className="bg-white rounded-lg p-3 border-2 border-green-200">
//                             <h5 className="font-semibold text-green-700 mb-2 text-sm">Nuevo</h5>
//                             <div className="space-y-1 text-xs">
//                                 <div className="flex justify-between">
//                                     <span>Vol:</span>
//                                     <span className="font-medium text-green-600">{calculation.volumenes?.volumen_nuevo || 0}</span>
//                                 </div>
//                                 <div className="flex justify-between">
//                                     <span>Total:</span>
//                                     <span className="font-medium text-green-600">
//                                         ${(calculation.costos_nuevos?.costo_total || 0).toLocaleString('es-CO', { 
//                                             minimumFractionDigits: 2, 
//                                             maximumFractionDigits: 2 
//                                         })}
//                                     </span>
//                                 </div>
//                                 <div className="flex justify-between">
//                                     <span>Precio:</span>
//                                     <span className="font-medium text-green-600">
//                                         ${(calculation.costos_nuevos?.precio_venta || 0).toLocaleString('es-CO', { 
//                                             minimumFractionDigits: 2, 
//                                             maximumFractionDigits: 2 
//                                         })}
//                                     </span>
//                                 </div>
//                             </div>
//                         </div>
//                     </div>

//                     {/* AGREGADO: Información adicional para debugging */}
//                     <div className="mt-3 p-2 bg-purple-200 rounded-lg flex justify-between text-xs">
//                             <div>
//                                 <p className="text-blue-800">
//                                     <strong>Factor:</strong> {calculation.volumenes?.factor_escala?.toFixed(4) || 0}
//                                 </p>
//                             </div>
//                             <div>
//                                 <p className="text-blue-800">
//                                     <strong>MP Total:</strong> {formatoPesoColombiano(calculation.costos_originales?.costo_total_materias_primas || 0)}
//                                 </p>
//                             </div>

//                     </div>

//                 </div>
//             )}
//         </div>
//     );
// };