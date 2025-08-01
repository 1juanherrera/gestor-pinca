// import { FaFlask, FaWeight, FaDollarSign } from 'react-icons/fa';
// import { MdScience } from 'react-icons/md';
// import { formatoPesoColombiano, formatoCantidad } from '../../utils/formatters';

// export const FormulacionesTable = ({ 
//     formulaciones, 
//     selectedProductData,
//     calculationResult = null,
//     compact = false
// }) => {
//     if (!selectedProductData) {
//         return (
//             <div className="bg-white rounded-lg shadow-sm p-4 text-center">
//                 <div className="text-gray-400 mb-3">
//                     <FaFlask size={compact ? 32 : 48} className="mx-auto" />
//                 </div>
//                 <h3 className={`${compact ? 'text-base' : 'text-lg'} font-medium text-gray-900 mb-2`}>
//                     Formulaciones
//                 </h3>
//                 <p className="text-sm text-gray-500">
//                     Selecciona un producto para ver sus formulaciones
//                 </p>
//             </div>
//         );
//     }

//     // Determinar qué formulaciones mostrar
//     const esCalculado = calculationResult && calculationResult.formulaciones_nuevas;
//     const formulacionesAMostrar = esCalculado ? 
//         calculationResult.formulaciones_nuevas : 
//         (formulaciones || []);

//     // Calcular totales
//     const totalCantidadCalculada = formulacionesAMostrar.reduce((sum, f) => {
//         return sum + (esCalculado ? (f.cantidad_nueva || 0) : (f.cantidad || 0));
//     }, 0);

//     const totalCostoCalculado = formulacionesAMostrar.reduce((sum, f) => {
//         return sum + (esCalculado ? (f.costo_total_materia_nueva || 0) : (f.costo_total_materia || 0));
//     }, 0);

//     // Función auxiliar para obtener la cantidad disponible
//     const getCantidadDisponible = (formulacion) => {
//         // Si es calculado, buscar en las formulaciones originales
//         if (esCalculado && formulaciones) {
//             const formulacionOriginal = formulaciones.find(f => 
//                 f.materia_prima_id === formulacion.materia_prima_id
//             );
//             return formulacionOriginal?.materia_prima?.cantidad_disponible || 
//                    formulacionOriginal?.cantidad_disponible || 0;
//         }
        
//         // Si no es calculado, usar la estructura normal
//         return formulacion.materia_prima?.cantidad_disponible || 
//                formulacion.cantidad_disponible || 0;
//     };

//     // Función auxiliar para determinar si hay suficiente stock
//     const tieneSuficienteStock = (formulacion) => {
//         const cantidadDisponible = getCantidadDisponible(formulacion);
//         const cantidadNecesaria = esCalculado ? 
//             (formulacion.cantidad_nueva || 0) : 
//             (formulacion.cantidad || 0);
        
//         return cantidadDisponible >= cantidadNecesaria;
//     };

//     return (
//         <div className="bg-white rounded-lg shadow-sm overflow-hidden">
//             {/* Header */}
//             <div className="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-3">
//                 <div className="flex items-center justify-between">
//                     <div>
//                         <h3 className={`${compact ? 'text-base' : 'text-lg'} font-semibold flex items-center gap-2`}>
//                             <FaFlask size={compact ? 16 : 20} />
//                             Formulaciones
//                             {esCalculado && (
//                                 <span className="bg-green-500 text-white text-xs px-2 py-1 rounded-sm">
//                                     Calculado
//                                 </span>
//                             )}
//                         </h3>
//                         <p className="text-blue-100 text-xs">
//                             {selectedProductData.nombre}
//                         </p>
//                     </div>
//                     <div className="text-right">
//                         <div className="text-xs text-blue-100">
//                             {esCalculado ? 
//                                 `Vol: ${calculationResult.volumenes?.volumen_nuevo || 0}` :
//                                 `Vol: ${selectedProductData.volumen_costo || 0}`
//                             }
//                         </div>
//                         <div className="text-xs text-blue-100">
//                             {formulacionesAMostrar.length} componentes
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
//                                 #
//                             </th>
//                             <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                 Materia Prima
//                             </th>
//                             <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                 <div className="flex items-center justify-center gap-1">
//                                     <FaWeight size={10} />
//                                     Cantidad
//                                 </div>
//                             </th>
//                              <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                 <div className="flex items-center justify-center gap-1">
//                                     <FaWeight size={10} />
//                                     Cantidad Disp.
//                                 </div>
//                             </th>
//                             <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                 <div className="flex items-center justify-center gap-1">
//                                     <FaDollarSign size={10} />
//                                     Costo Unit.
//                                 </div>
//                             </th>
//                             <th className="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                 <div className="flex items-center justify-center gap-1">
//                                     <FaDollarSign size={10} />
//                                     Costo Total
//                                 </div>
//                             </th>
//                         </tr>
//                     </thead>
//                     <tbody className="bg-white divide-y divide-gray-200">
//                         {formulacionesAMostrar.length > 0 ? (
//                             formulacionesAMostrar.map((formulacion, index) => {
//                                 const cantidadDisponible = getCantidadDisponible(formulacion);
//                                 const suficienteStock = tieneSuficienteStock(formulacion);
                                
//                                 return (
//                                     <tr key={formulacion.id || index} className="hover:bg-gray-50">
//                                         <td className="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
//                                             {index + 1}
//                                         </td>
//                                         <td className="px-3 py-2 whitespace-nowrap">
//                                             <div className="flex items-center">
//                                                 <div className="flex-shrink-0 h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center">
//                                                     <MdScience className="h-3 w-3 text-blue-600" />
//                                                 </div>
//                                                 <div className="ml-3">
//                                                     <div className="text-sm font-medium text-gray-900">
//                                                         {formulacion.materia_prima_nombre}
//                                                     </div>
//                                                     <div className="text-xs text-gray-500">
//                                                         {formulacion.materia_prima_codigo}
//                                                     </div>
//                                                 </div>
//                                             </div>
//                                         </td>
//                                         <td className="px-3 py-2 whitespace-nowrap text-center">
//                                             <div className={`text-sm font-semibold ${esCalculado ? 'text-green-600' : 'text-blue-600'}`}>
//                                                 {esCalculado ? 
//                                                     formatoCantidad(formulacion.cantidad_nueva || 0) : 
//                                                     formatoCantidad(formulacion.cantidad || 0)
//                                                 }
//                                             </div>
//                                             {/* Mostrar cantidad original si es calculado */}
//                                             {esCalculado && (
//                                                 <div className="text-xs text-gray-400">
//                                                     Origen: {formatoCantidad(formulacion.cantidad_original || formulacion.cantidad || 0)}
//                                                 </div>
//                                             )}
//                                         </td>
//                                         <td className="px-3 py-2 whitespace-nowrap text-center">
//                                             <div className={`${suficienteStock ? 'text-green-600' : 'text-red-600'} text-sm font-semibold`}>
//                                                 {formatoCantidad(cantidadDisponible)}
//                                             </div>
//                                             {/* Mostrar estado del stock */}
//                                             {!suficienteStock && cantidadDisponible > 0 && (
//                                                 <div className="text-xs text-red-500">
//                                                     Insuficiente
//                                                 </div>
//                                             )}
//                                         </td>
//                                         <td className="px-3 py-2 whitespace-nowrap text-center">
//                                             <div className="text-sm font-semibold text-gray-600">
//                                                 {formatoPesoColombiano(formulacion.materia_prima_costo_unitario || 0)}
//                                             </div>
//                                         </td>
//                                         <td className="px-3 py-2 whitespace-nowrap text-center">
//                                             <div className={`text-sm font-semibold ${esCalculado ? 'text-green-600' : 'text-emerald-600'}`}>
//                                                 {esCalculado ? 
//                                                     formatoPesoColombiano(formulacion.costo_total_materia_nueva || 0) : 
//                                                     formatoPesoColombiano(formulacion.costo_total_materia || 0)
//                                                 }
//                                             </div>
//                                             {/* Mostrar costo original si es calculado */}
//                                             {esCalculado && (
//                                                 <div className="text-xs text-gray-400">
//                                                     Origen: {formatoPesoColombiano(formulacion.costo_total_materia || 0)}
//                                                 </div>
//                                             )}
//                                         </td>
//                                     </tr>
//                                 );
//                             })
//                         ) : (
//                             <tr>
//                                 <td colSpan="6" className="px-3 py-8 text-center text-gray-500">
//                                     <div className="flex flex-col items-center">
//                                         <FaFlask size={48} className="text-gray-300 mb-2" />
//                                         <p className="text-sm">
//                                             No hay formulaciones disponibles
//                                         </p>
//                                     </div>
//                                 </td>
//                             </tr>
//                         )}
//                     </tbody>
//                 </table>
//             </div>

//             {/* Footer */}
//             <div className="bg-gray-50 px-4 py-3 border-t border-gray-400">
//                 <div className="flex justify-end items-center">
//                     <div className="flex gap-4">
//                         <div className="text-sm">
//                             <span className="text-gray-600">Total Cantidad: </span>
//                             <span className={`font-semibold ${esCalculado ? 'text-green-600' : 'text-blue-600'}`}>
//                                 {formatoCantidad(totalCantidadCalculada)}
//                             </span>
//                         </div>
//                         <div className="text-sm">
//                             <span className="text-gray-600">Total Costo: </span>
//                             <span className={`font-semibold ${esCalculado ? 'text-green-600' : 'text-emerald-600'}`}>
//                                 {formatoPesoColombiano(totalCostoCalculado)}
//                             </span>
//                         </div>
//                     </div>
//                 </div>
//             </div>
//         </div>
//     );
// };