// import { FaTimes, FaTrash, FaExclamationTriangle } from 'react-icons/fa';

// export const DeleteConfirmModal = ({ 
//   isOpen, 
//   onConfirm, 
//   onCancel, 
//   itemName, 
//   itemType = 'elemento',
//   message,
//   confirmText = 'Eliminar',
//   cancelText = 'Cancelar',
//   loading = false 
// }) => {
//   if (!isOpen) return null;

//   const defaultMessage = `¿Estás seguro de que deseas eliminar ${itemType === 'cliente' ? 'el cliente' : `${itemType}`} "${itemName}"?`;
//   const displayMessage = message || defaultMessage;

//   return (
//     <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
//       <div className="bg-white rounded-lg shadow-xl w-full max-w-md">
//         {/* Header */}
//         <div className="flex items-center justify-between p-6 border-b border-gray-200">
//           <div className="flex items-center">
//             <div className="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
//               <FaExclamationTriangle className="text-red-600" size={20} />
//             </div>
//             <div className="ml-4">
//               <h3 className="text-lg font-medium text-gray-900">
//                 Confirmar eliminación
//               </h3>
//             </div>
//           </div>
//           <button
//             onClick={onCancel}
//             disabled={loading}
//             className="text-gray-400 hover:text-gray-600 transition-colors"
//           >
//             <FaTimes size={20} />
//           </button>
//         </div>

//         {/* Content */}
//         <div className="p-6">
//           <div className="text-gray-700">
//             <p className="mb-4">{displayMessage}</p>
            
//             {itemType === 'cliente' && (
//               <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
//                 <div className="flex">
//                   <FaExclamationTriangle className="text-yellow-400 mt-0.5 mr-3" size={16} />
//                   <div className="text-sm text-yellow-800">
//                     <p className="font-medium mb-2">Importante:</p>
//                     <ul className="list-disc list-inside space-y-1">
//                       <li>Esta acción no se puede deshacer</li>
//                       <li>No podrás eliminar si tiene facturas o pagos asociados</li>
//                       <li>Se perderá toda la información del cliente</li>
//                     </ul>
//                   </div>
//                 </div>
//               </div>
//             )}
            
//             <p className="text-sm text-gray-500">
//               Esta acción no se puede deshacer.
//             </p>
//           </div>
//         </div>

//         {/* Footer */}
//         <div className="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 rounded-b-lg">
//           <button
//             type="button"
//             onClick={onCancel}
//             disabled={loading}
//             className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors"
//           >
//             {cancelText}
//           </button>
          
//           <button
//             type="button"
//             onClick={onConfirm}
//             disabled={loading}
//             className="flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 transition-colors"
//           >
//             {loading ? (
//               <>
//                 <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
//                 Eliminando...
//               </>
//             ) : (
//               <>
//                 <FaTrash className="mr-2" size={14} />
//                 {confirmText}
//               </>
//             )}
//           </button>
//         </div>
//       </div>
//     </div>
//   );
// };