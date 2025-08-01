// import { FaBox, FaEdit, FaTrash, FaEye, FaBuilding } from 'react-icons/fa';

// export const ItemProveedorCard = ({ itemProveedor, onEdit, onDelete, onView }) => {
//     const formatPrice = (price) => {
//         return new Intl.NumberFormat('es-CO', {
//             style: 'currency',
//             currency: 'COP'
//         }).format(price || 0);
//     };

//     const getStatusColor = (cantidad) => {
//         if (cantidad === 0) return 'bg-red-100 text-red-800';
//         if (cantidad < 10) return 'bg-yellow-100 text-yellow-800';
//         return 'bg-green-100 text-green-800';
//     };

//     const getStatusText = (cantidad) => {
//         if (cantidad === 0) return 'Sin stock';
//         if (cantidad < 10) return 'Stock bajo';
//         return 'En stock';
//     };

//     return (
//         <tr className="border-b border-gray-300 hover:bg-gray-200 transition-colors">
//             <td className="px-2 py-2 text-xs font-medium text-gray-900 border border-gray-200 max-w-[160px] whitespace-nowrap overflow-hidden text-ellipsis">
//                 <div className="flex items-center gap-2 min-w-0">
//                     <FaBox className="text-green-600 shrink-0" />
//                     <span className="truncate block uppercase">{itemProveedor.nombre_item}</span>
//                 </div>
//             </td>
//             <td className="px-2 py-2 text-xs text-gray-900 border border-gray-200 max-w-[100px] whitespace-nowrap overflow-hidden text-ellipsis">
//                 <span className="truncate block">{itemProveedor.codigo_item}</span>
//             </td>
//             <td className="px-2 py-2 text-xs text-gray-900 border border-gray-200 max-w-[140px] whitespace-nowrap overflow-hidden text-ellipsis">
//                 <div className="flex items-center gap-2 min-w-0">
//                     <span className="truncate block">
//                         {itemProveedor.nombre_proveedor || <span className="text-gray-400">No especificado</span>}
//                     </span>
//                 </div>
//             </td>
//             <td className="px-2 py-2 text-xs font-semibold text-left text-emerald-800 border border-gray-200 max-w-[110px] whitespace-nowrap overflow-hidden text-ellipsis">
//                 <p className='ml-4'>{formatPrice(itemProveedor.precio_unitario)}</p>
//             </td>
//             <td className="px-2 py-2 text-xs font-semibold text-emerald-800 border border-gray-200 max-w-[110px] whitespace-nowrap overflow-hidden text-ellipsis text-left">
//                 <p className='ml-4'>{formatPrice(itemProveedor.precio_con_iva)}</p>
//             </td>
//             <td className="px-2 py-2 text-xs text-gray-900 text-center border border-gray-200 max-w-[80px] whitespace-nowrap overflow-hidden text-ellipsis">
//                 <span className="truncate block uppercase">
//                     {itemProveedor.unidad_empaque || <span className="text-gray-400">No especificada</span>}
//                 </span>
//             </td>
//             <td className="px-2 py-2 text-center border border-gray-200 max-w-[90px] whitespace-nowrap overflow-hidden text-ellipsis">
//                 <span className={`px-3 py-1 text-xs font-medium rounded-md ${getStatusColor(itemProveedor.cantidad)}`}>
//                     {getStatusText(itemProveedor.cantidad)}
//                 </span>
//             </td>
//             <td className="px-2 py-1 border border-gray-200 min-w-[110px]">
//                 <div className="flex justify-center gap-2">
//                     {onEdit && (
//                         <button
//                             onClick={() => onEdit(itemProveedor)}
//                             className="p-2 text-white rounded-md transition-colors bg-gray-500 hover:bg-gray-800 cursor-pointer"
//                             title="Editar"
//                         >
//                             <FaEdit size={14} />
//                         </button>
//                     )}
//                     {onView && (
//                         <button
//                             onClick={() => onView(itemProveedor)}
//                             className="p-2 text-white rounded-md transition-colors bg-green-600 hover:bg-green-800 cursor-pointer"
//                             title="Ver detalles"
//                         >
//                             <FaEye size={14} />
//                         </button>
//                     )}
//                     {onDelete && (
//                         <button
//                             onClick={() => onDelete(itemProveedor)}
//                             className="p-2 bg-red-500 text-white hover:bg-red-800 rounded-md transition-colors cursor-pointer"
//                             title="Eliminar"
//                         >
//                             <FaTrash size={14} />
//                         </button>
//                     )}
//                 </div>
//             </td>
//         </tr>
//     );
// };