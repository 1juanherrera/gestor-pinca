// import { FaDollarSign, FaFlask, FaBox, FaTag, FaPallet } from 'react-icons/fa';
// import { MdWork, MdCalculate } from 'react-icons/md';
// import { FaBottleWater } from "react-icons/fa6";
// import { formatoPesoColombiano } from '../../utils/formatters';

// export const CostProductsTable = ({ 
//     selectedProductData,
//     calculationResult = null,
//     compact = false 
// }) => {
//     if (!selectedProductData) {
//         return (
//             <div className="bg-white rounded-lg shadow-sm p-4 text-center">
//                 <div className="text-gray-400 mb-3">
//                     <MdCalculate size={48} className="mx-auto" />
//                 </div>
//                 <h3 className='text-lg font-medium text-gray-900 mb-2'>
//                     Desglose de Costos
//                 </h3>
//                 <p className="text-sm text-gray-500">
//                     Selecciona un producto para ver su desglose de costos
//                 </p>
//             </div>
//         );
//     }

//     // Determinar si usar datos calculados o originales
//     const esCalculado = calculationResult && calculationResult.costos_nuevos;
    
//     // Definir los conceptos de costo con lógica especial para escalado
//     const costConcepts = [
//         { 
//             key: 'costo_mp_galon', 
//             value: esCalculado ? 
//                 (calculationResult.costos_nuevos.costo_mp_galon || 0) : 
//                 (selectedProductData.costo_mp_galon || 0),
//             isScaled: true  // Este valor SÍ se escala
//         },
//         { 
//             key: 'costo_mod', 
//             value: selectedProductData.costo_mod || 0,
//             isScaled: false  // Este valor NO se escala (siempre el mismo)
//         },
//         { 
//             key: 'envase', 
//             value: selectedProductData.envase || 0,
//             isScaled: false  // Este valor NO se escala (siempre el mismo)
//         },
//         { 
//             key: 'etiqueta', 
//             value: selectedProductData.etiqueta || 0,
//             isScaled: false  // Este valor NO se escala (siempre el mismo)
//         },
//         { 
//             key: 'bandeja', 
//             value: selectedProductData.bandeja || 0,
//             isScaled: false  // Este valor NO se escala (siempre el mismo)
//         },
//         { 
//             key: 'plastico', 
//             value: selectedProductData.plastico || 0,
//             isScaled: false  // Este valor NO se escala (siempre el mismo)
//         }
//     ];

//     // Mapear los conceptos de costo con sus iconos
//     const getCostIcon = (concept) => {
//         switch(concept.toLowerCase()) {
//             case 'costo_mp_galon':
//                 return <FaFlask className="text-blue-500" size={14} />;
//             case 'costo_mod':
//                 return <MdWork className="text-green-500" size={14} />;
//             case 'envase':
//                 return <FaBox className="text-orange-500" size={14} />;
//             case 'etiqueta':
//                 return <FaTag className="text-red-500" size={14} />;
//             case 'bandeja':
//                 return <FaPallet className="text-purple-500" size={14} />;
//             case 'plastico':
//                 return <FaBottleWater className="text-teal-500" size={14} />;
//             default:
//                 return <FaDollarSign className="text-gray-500" size={14} />;
//         }
//     };

//     // Formatear el nombre del concepto
//     const formatCostName = (concept) => {
//         const names = {
//             'costo_mp_galon': 'COSTO MP/GALÓN',
//             'costo_mod': 'COSTO MOD',
//             'envase': 'ENVASE',
//             'etiqueta': 'ETIQUETA',
//             'bandeja': 'BANDEJA',
//             'plastico': 'PLÁSTICO'
//         };
//         return names[concept] || concept.toUpperCase();
//     };

//     // Calcular el costo total como suma de todos los componentes
//     const costoTotal = costConcepts.reduce((sum, concept) => sum + (concept.value || 0), 0);

//     // Calcular costo total original para comparación (si es calculado)
//     const costoTotalOriginal = esCalculado ? 
//         (selectedProductData.costo_mp_galon || 0) + 
//         (selectedProductData.costo_mod || 0) + 
//         (selectedProductData.envase || 0) + 
//         (selectedProductData.etiqueta || 0) + 
//         (selectedProductData.bandeja || 0) + 
//         (selectedProductData.plastico || 0) : 0;

//     return (
//         <div className="bg-white rounded-lg shadow-sm overflow-hidden">
//             {/* Header */}
//             <div className="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-4 py-3">
//                 <div className="flex items-center justify-between">
//                     <div>
//                         <h3 className={`${compact ? 'text-base' : 'text-lg'} font-semibold flex items-center gap-2`}>
//                             <MdCalculate size={compact ? 16 : 20} />
//                             Desglose de Costos
//                             {esCalculado && (
//                                 <span className="bg-green-500 text-white text-xs px-2 py-1 rounded-sm">
//                                     Calculado
//                                 </span>
//                             )}
//                         </h3>
//                         <p className="text-emerald-100 text-xs">
//                             {selectedProductData.nombre}
//                         </p>
//                     </div>
//                     <div className="text-right">
//                         <div className="text-xs text-emerald-100">
//                             {esCalculado ? 
//                                 `Vol: ${calculationResult.volumenes?.volumen_nuevo || 0}` :
//                                 `Vol: ${selectedProductData.volumen_costo || 0}`
//                             }
//                         </div>
//                         <div className="text-xs text-emerald-100">
//                             {selectedProductData.codigo}
//                         </div>
//                     </div>
//                 </div>
//             </div>

//             {/* Tabla */}
//             <div className="overflow-x-auto">
//                 <table className="w-full">
//                     <thead className="bg-gray-50">
//                         <tr>
//                             <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                 Concepto
//                             </th>
//                             <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                 <div className="flex items-center justify-center gap-1">
//                                     <FaDollarSign size={10} />
//                                     Valor
//                                 </div>
//                             </th>  
//                             <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                 <div className="flex items-center justify-center gap-1">
//                                     <FaDollarSign size={10} />
//                                     Original
//                                 </div>
//                             </th>
//                         </tr>
//                     </thead>
//                     <tbody className="bg-white divide-y divide-gray-200">
//                         {costConcepts.map((concept) => (
//                             <tr key={concept.key} className="hover:bg-gray-50">
//                                 <td className="px-3 py-2 whitespace-nowrap">
//                                     <div className="flex items-center">
//                                         <div className="flex-shrink-0 mr-3">
//                                             {getCostIcon(concept.key)}
//                                         </div>
//                                         <div className="text-sm font-medium text-gray-900">
//                                             {formatCostName(concept.key)}
//                                             {/* Indicador si el valor se escala */}
//                                             {concept.isScaled && esCalculado && (
//                                                 <span className="ml-1 text-xs text-green-600">●</span>
//                                             )}
//                                         </div>
//                                     </div>
//                                 </td>
//                                 <td className="px-3 py-2 whitespace-nowrap text-center">
//                                     <div className={`text-sm font-semibold ${
//                                         concept.value > 0 ? 
//                                             (concept.isScaled && esCalculado ? 'text-green-600' : 'text-emerald-600') : 
//                                             'text-gray-400'
//                                     }`}>
//                                         {concept.value > 0 ? formatoPesoColombiano(concept.value) : '-'}
//                                     </div>
//                                 </td>
//                                 {/* COLUMNA ORIGINAL CORREGIDA */}
//                                 <td className="px-3 py-2 whitespace-nowrap text-center">
//                                     <div className="text-xs text-gray-400">
//                                         {selectedProductData[concept.key] > 0 ? 
//                                             formatoPesoColombiano(selectedProductData[concept.key]) : '-'
//                                         }
//                                     </div>
//                                 </td>
//                             </tr>
//                         ))}
                        
//                         {/* Fila del total */}
//                         <tr className="bg-gray-100 font-semibold border-t-2 border-gray-300">
//                             <td className="px-3 py-3 whitespace-nowrap">
//                                 <div className="flex items-center">
//                                     <div className="flex-shrink-0 mr-3">
//                                         <FaDollarSign className="text-emerald-600" size={16} />
//                                     </div>
//                                     <div className="text-sm font-bold text-gray-900">
//                                         COSTO TOTAL
//                                     </div>
//                                 </div>
//                             </td>
//                             <td className="px-3 py-3 whitespace-nowrap text-center">
//                                 <div className={`text-lg font-bold ${esCalculado ? 'text-green-700' : 'text-emerald-700'}`}>
//                                     {formatoPesoColombiano(costoTotal * 1.4)}
//                                 </div>
//                             </td>
//                             <td className="px-3 py-3 whitespace-nowrap text-center">
//                                 <div className="text-sm font-medium text-gray-600">
//                                     {formatoPesoColombiano(costoTotalOriginal)}
//                                 </div>
//                             </td>
//                         </tr>
//                     </tbody>
//                 </table>
//             </div>

//             {/* Footer */}
//             <div className="bg-gray-50 px-4 py-3 border-t border-gray-400">
//                 <div className="flex justify-between items-center">
//                     <div className="text-sm text-gray-600">
//                         <span className="font-semibold">Fecha:</span> {selectedProductData.fecha_calculo || 'N/A'}
//                     </div>
//                 </div>
//             </div>
//         </div>
//     );
// };