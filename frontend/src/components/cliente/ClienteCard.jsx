// import { 
//   FaBuilding, 
//   FaPhone, 
//   FaEnvelope, 
//   FaIdCard, 
//   FaMapMarkerAlt,
//   FaEye,
//   FaEdit,
//   FaTrash,
//   FaFileInvoiceDollar,
//   FaExclamationTriangle,
//   FaChartLine
// } from 'react-icons/fa';
// import { formatoPesoColombiano } from '../../utils/formatters';

// export const ClienteCard = ({ cliente, onView, onEdit, onDelete }) => {

//   return (
//     <div className="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
//       {/* Header de la tarjeta */}
//       <div className="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-t-lg">
//         <div className="flex items-start justify-between">
//           <div className="flex items-center">
//             <div className="bg-white bg-opacity-20 p-2 rounded-lg">
//               <FaBuilding size={20} />
//             </div>
//             <div className="ml-3">
//               <h3 className="font-semibold text-lg leading-tight">
//                 {cliente.nombre_empresa || 'Sin nombre de empresa'}
//               </h3>
//               <p className="text-blue-100 text-sm">
//                 {cliente.nombre_encargado || 'Sin encargado asignado'}
//               </p>
//             </div>
//           </div>
          
//           {/* Estado del cliente */}
//           <div className="flex flex-col items-end gap-1">
            
//             {/* Indicador de facturas pendientes */}
//             {facturasPendientes === 0 && saldoPendiente > 0 && (
//               <span className="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
//                 Pendiente
//               </span>
//             )}
            
//             {/* Indicador de saldo al día */}
//             {facturasPendientes === 0 && saldoPendiente === 0 && totalFacturado > 0 && (
//               <span className="bg-green-500 text-white text-xs px-2 py-1 rounded-full">
//                 Al día
//               </span>
//             )}
//           </div>
//         </div>
//       </div>

//       {/* Contenido de la tarjeta */}
//       <div className="p-4">
//         {/* Información de contacto */}
//         <div className="space-y-2 mb-4">
//           <div className="flex items-center text-sm text-gray-600">
//             <FaIdCard className="w-4 h-4 mr-2 text-gray-400" />
//             <span className="font-medium">Doc:</span>
//             <span className="ml-1">{cliente.numero_documento || 'No registrado'}</span>
//           </div>
          
//           {cliente.telefono ? (
//             <div className="flex items-center text-sm text-gray-600">
//               <FaPhone className="w-4 h-4 mr-2 text-green-500" />
//               <span>{cliente.telefono}</span>
//             </div>
//           ) : (
//             <div className="flex items-center text-sm text-gray-400">
//               <FaPhone className="w-4 h-4 mr-2" />
//               <span>Sin teléfono</span>
//             </div>
//           )}
          
//           {cliente.email ? (
//             <div className="flex items-center text-sm text-gray-600">
//               <FaEnvelope className="w-4 h-4 mr-2 text-green-500" />
//               <span className="truncate">{cliente.email}</span>
//             </div>
//           ) : (
//             <div className="flex items-center text-sm text-gray-400">
//               <FaEnvelope className="w-4 h-4 mr-2" />
//               <span>Sin email</span>
//             </div>
//           )}
          
//           {cliente.direccion ? (
//             <div className="flex items-start text-sm text-gray-600">
//               <FaMapMarkerAlt className="w-4 h-4 mr-2 mt-0.5 text-green-500" />
//               <span className="line-clamp-2">{cliente.direccion}</span>
//             </div>
//           ) : (
//             <div className="flex items-center text-sm text-gray-400">
//               <FaMapMarkerAlt className="w-4 h-4 mr-2" />
//               <span>Sin dirección</span>
//             </div>
//           )}
//         </div>

//         {/* Estadísticas financieras - Igual que en el modal */}
//         {totalFacturado > 0 && (
//           <div className="border-t pt-3 mb-4">
//             <h4 className="text-sm font-medium text-gray-700 mb-3 flex items-center">
//               <FaChartLine className="mr-1" />
//               Estadísticas
//             </h4>
            
//             <div className="grid grid-cols-2 gap-2">
//               {/* Total Facturas */}
//               <div className="bg-blue-50 p-3 rounded-lg">
//                 <div className="text-lg font-bold text-blue-600">
//                   {totalFacturas}
//                 </div>
//                 <div className="text-xs text-blue-800">Total Facturas</div>
//               </div>

//               {/* Total Facturado */}
//               <div className="bg-green-50 p-3 rounded-lg">
//                 <div className="text-sm font-bold text-green-600">
//                   {formatoPesoColombiano(totalFacturado)}
//                 </div>
//                 <div className="text-xs text-green-800">Total Facturado</div>
//               </div>

//               {/* Total Pagado */}
//               <div className="bg-purple-50 p-3 rounded-lg">
//                 <div className="text-sm font-bold text-purple-600">
//                   {formatoPesoColombiano(totalPagado)}
//                 </div>
//                 <div className="text-xs text-purple-800">Total Pagado</div>
//               </div>

//               {/* Saldo Pendiente - Destacado */}
//               <div className={`p-3 rounded-lg ${
//                 saldoPendiente > 0 ? 'bg-red-50' : 'bg-green-50'
//               }`}>
//                 <div className={`text-sm font-bold ${
//                   saldoPendiente > 0 ? 'text-red-600' : 'text-green-600'
//                 }`}>
//                   {formatoPesoColombiano(saldoPendiente)}
//                 </div>
//                 <div className={`text-xs ${
//                   saldoPendiente > 0 ? 'text-red-800' : 'text-green-800'
//                 }`}>
//                   Saldo Pendiente
//                 </div>
//               </div>
//             </div>
//           </div>
//         )}

//         {/* Cliente sin facturación */}
//         {totalFacturado === 0 && (
//           <div className="border-t pt-3 mb-4">
//             <div className="bg-gray-50 p-3 rounded-lg text-center">
//               <FaFileInvoiceDollar className="mx-auto mb-2 text-gray-400" size={24} />
//               <div className="text-sm text-gray-600">Cliente nuevo</div>
//               <div className="text-xs text-gray-500">Sin facturación registrada</div>
//             </div>
//           </div>
//         )}

//         {/* Alertas basadas en saldo */}
//         {saldoPendiente > 500000 && (
//           <div className="bg-red-50 border border-red-200 rounded-lg p-2 mb-4">
//             <div className="flex items-center text-red-700 text-xs">
//               <FaExclamationTriangle className="mr-2" size={12} />
//               <span>⚠️ Saldo crítico - Requiere seguimiento urgente</span>
//             </div>
//           </div>
//         )}

//         {facturasPendientes >= 3 && (
//           <div className="bg-orange-50 border border-orange-200 rounded-lg p-2 mb-4">
//             <div className="flex items-center text-orange-700 text-xs">
//               <FaExclamationTriangle className="mr-2" size={12} />
//               <span>📄 Múltiples facturas pendientes</span>
//             </div>
//           </div>
//         )}

//         {!cliente.numero_documento && (
//           <div className="bg-blue-50 border border-blue-200 rounded-lg p-2 mb-4">
//             <div className="flex items-center text-blue-700 text-xs">
//               <FaExclamationTriangle className="mr-2" size={12} />
//               <span>📋 Completar documento para facturación</span>
//             </div>
//           </div>
//         )}
//       </div>

//       {/* Footer con acciones */}
//       <div className="bg-gray-50 px-4 py-3 rounded-b-lg">
//         <div className="flex items-center justify-between">
//           <div className="text-xs text-gray-500">
//             ID: {cliente.id}
//           </div>
          
//           <div className="flex space-x-2">
//             <button
//               onClick={() => onView(cliente)}
//               className="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors"
//               title="Ver detalles y estadísticas completas"
//             >
//               <FaEye size={14} />
//             </button>
            
//             <button
//               onClick={() => onEdit(cliente)}
//               className="flex items-center justify-center w-8 h-8 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors"
//               title="Editar información"
//             >
//               <FaEdit size={14} />
//             </button>
            
//             <button
//               onClick={() => onDelete(cliente)}
//               className="flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors"
//               title="Eliminar cliente"
//             >
//               <FaTrash size={14} />
//             </button>
//           </div>
//         </div>
//       </div>
//     </div>
//   );
// };