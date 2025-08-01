// import React from 'react';
// import { FaBuilding, FaUser, FaPhone, FaEnvelope, FaMapMarkerAlt, FaEdit, FaTrash, FaEye, FaBox } from 'react-icons/fa';

// export const ProveedorCard = ({ proveedor, onEdit, onDelete, onView }) => {
//     return (
//         <div className="bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200">
//             <div className="p-6">
//                 {/* Header */}
//                 <div className="flex items-start justify-between mb-4">
//                     <div className="flex items-center gap-3">
//                         <div className="bg-blue-100 p-2 rounded-lg">
//                             <FaBuilding className="text-blue-600" size={20} />
//                         </div>
//                         <div>
//                             <h3 className="text-lg font-semibold text-gray-900 truncate">
//                                 {proveedor.nombre_empresa}
//                             </h3>
//                             <p className="text-sm text-gray-600">
//                                 {proveedor.numero_documento}
//                             </p>
//                         </div>
//                     </div>
//                     <div className="flex items-center gap-1">
//                         <button
//                             onClick={() => onEdit(proveedor)}
//                             className="p-2 text-blue-600 cursor-pointer hover:bg-blue-300 bg-blue-100 rounded-lg transition-colors"
//                             title="Editar proveedor"
//                         >
//                             <FaEdit size={14} />
//                         </button>
//                         {onView && (
//                             <button
//                                 onClick={() => onView(proveedor)}
//                                 className="p-2 text-green-600 hover:bg-green-300 bg-green-100 rounded-lg transition-colors"
//                                 title="Ver detalles"
//                             >
//                                 <FaEye size={14} />
//                             </button>
//                         )}
//                         <button
//                             onClick={() => onDelete(proveedor)}
//                             className="p-2 text-red-600 hover:bg-red-300 cursor-pointer bg-red-100 rounded-lg transition-colors"
//                             title="Eliminar proveedor"
//                         >
//                             <FaTrash size={14} />
//                         </button>
//                     </div>
//                 </div>

//                 {/* Información del encargado */}
//                 {proveedor.nombre_encargado && (
//                     <div className="flex items-center gap-2 mb-3">
//                         <FaUser className="text-gray-400" size={14} />
//                         <span className="text-sm text-gray-700">
//                             {proveedor.nombre} 
//                         </span>
//                     </div>
//                 )}

//                 {/* Información de contacto */}
//                 <div className="space-y-2 mb-4">
//                     {proveedor.telefono && (
//                         <div className="flex items-center gap-2">
//                             <FaPhone className="text-gray-400" size={12} />
//                             <span className="text-sm text-gray-600">
//                                 {proveedor.telefono}
//                             </span>
//                         </div>
//                     )}
                    
//                     {proveedor.email && (
//                         <div className="flex items-center gap-2">
//                             <FaEnvelope className="text-gray-400" size={12} />
//                             <span className="text-sm text-gray-600 truncate">
//                                 {proveedor.email}
//                             </span>
//                         </div>
//                     )}
                    
//                     {proveedor.direccion && (
//                         <div className="flex items-start gap-2">
//                             <FaMapMarkerAlt className="text-gray-400 mt-0.5" size={12} />
//                             <span className="text-sm text-gray-600 line-clamp-2">
//                                 {proveedor.direccion}
//                             </span>
//                         </div>
//                     )}
//                 </div>

//                 {/* Estadísticas */}
//                 <div className="border-t pt-3">
//                     <div className="grid grid-cols-2 gap-4">
//                         <div className="text-center">
//                             <div className="flex items-center justify-center gap-1 mb-1">
//                                 <FaBox className="text-blue-500" size={14} />
//                                 <span className="text-xs text-gray-600">Productos</span>
//                             </div>
//                             <span className="text-lg font-semibold text-blue-600">
//                                 {proveedor.total_productos || 0}
//                             </span>
//                         </div>
                        
//                         <div className="text-center">
//                             <div className="flex items-center justify-center gap-1 mb-1">
//                                 <span className="text-xs text-gray-600">Disponibles</span>
//                             </div>
//                             <span className="text-lg font-semibold text-green-600">
//                                 {proveedor.productos_disponibles || 0}
//                             </span>
//                         </div>
//                     </div>
                    
//                     {proveedor.precio_promedio > 0 && (
//                         <div className="mt-2 text-center">
//                             <span className="text-xs text-gray-600">Precio promedio: </span>
//                             <span className="text-sm font-medium text-gray-900">
//                                 ${proveedor.precio_promedio.toLocaleString('es-CO')}
//                             </span>
//                         </div>
//                     )}
//                 </div>
//             </div>
//         </div>
//     );
// };