// import { 
//   FaTimes, 
//   FaUser, 
//   FaBuilding, 
//   FaIdCard, 
//   FaMapMarkerAlt, 
//   FaPhone, 
//   FaEnvelope,
//   FaSave,
//   FaFileInvoiceDollar,
//   FaMoneyBillWave,
//   FaChartLine,
//   FaSpinner
// } from 'react-icons/fa';
// import { formatoPesoColombiano } from '../../utils/formatters';

// export const ClienteModal = ({ isOpen, onClose, mode, cliente, onSave }) => {

//   return (
//     <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
//       <div className="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
//         {/* Header */}
//         <div className="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4">
//           <div className="flex items-center justify-between">
//             <h2 className="text-xl font-semibold flex items-center gap-2">
//               <FaUser />
//               {modalTitle}
//             </h2>
//             <button
//               onClick={onClose}
//               className="text-white hover:text-gray-200 transition-colors"
//             >
//               <FaTimes size={20} />
//             </button>
//           </div>
//         </div>

//         {/* Tabs (solo en modo view) */}
//         {mode === 'view' && (
//           <div className="border-b border-gray-200">
//             <div className="flex">
//               <button
//                 onClick={() => setActiveTab('info')}
//                 className={`px-6 py-3 text-sm font-medium border-b-2 ${
//                   activeTab === 'info'
//                     ? 'border-blue-500 text-blue-600'
//                     : 'border-transparent text-gray-500 hover:text-gray-700'
//                 }`}
//               >
//                 <FaUser className="inline mr-2" />
//                 Información
//               </button>
//               <button
//                 onClick={() => setActiveTab('facturas')}
//                 className={`px-6 py-3 text-sm font-medium border-b-2 ${
//                   activeTab === 'facturas'
//                     ? 'border-blue-500 text-blue-600'
//                     : 'border-transparent text-gray-500 hover:text-gray-700'
//                 }`}
//               >
//                 <FaFileInvoiceDollar className="inline mr-2" />
//                 Facturas ({clienteConFacturas?.facturas?.length || 0})
//               </button>
//               <button
//                 onClick={() => setActiveTab('pagos')}
//                 className={`px-6 py-3 text-sm font-medium border-b-2 ${
//                   activeTab === 'pagos'
//                     ? 'border-blue-500 text-blue-600'
//                     : 'border-transparent text-gray-500 hover:text-gray-700'
//                 }`}
//               >
//                 <FaMoneyBillWave className="inline mr-2" />
//                 Pagos ({clienteConPagos?.pagos?.length || 0})
//               </button>
//               <button
//                 onClick={() => setActiveTab('estadisticas')}
//                 className={`px-6 py-3 text-sm font-medium border-b-2 ${
//                   activeTab === 'estadisticas'
//                     ? 'border-blue-500 text-blue-600'
//                     : 'border-transparent text-gray-500 hover:text-gray-700'
//                 }`}
//               >
//                 <FaChartLine className="inline mr-2" />
//                 Estadísticas
//               </button>
//             </div>
//           </div>
//         )}

//         {/* Content */}
//         <div className="p-6 max-h-[70vh] overflow-y-auto">
//           {activeTab === 'info' ? (
//             /* Formulario de información */
//             <form onSubmit={handleSubmit} className="space-y-6">
//               {errors.submit && (
//                 <div className="bg-red-50 border border-red-200 rounded-lg p-4">
//                   <p className="text-red-800">{errors.submit}</p>
//                 </div>
//               )}

//               <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
//                 {/* Nombre de la empresa */}
//                 <div>
//                   <label className="block text-sm font-medium text-gray-700 mb-2">
//                     <FaBuilding className="inline mr-2" />
//                     Nombre de la Empresa *
//                   </label>
//                   <input
//                     type="text"
//                     name="nombre_empresa"
//                     value={formData.nombre_empresa}
//                     onChange={handleChange}
//                     disabled={isReadOnly}
//                     className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
//                       errors.nombre_empresa ? 'border-red-500' : 'border-gray-300'
//                     } ${isReadOnly ? 'bg-gray-50' : ''}`}
//                     placeholder="Ej: Pinturas ABC S.A.S"
//                   />
//                   {errors.nombre_empresa && (
//                     <p className="text-red-500 text-sm mt-1">{errors.nombre_empresa}</p>
//                   )}
//                 </div>

//                 {/* Nombre del encargado */}
//                 <div>
//                   <label className="block text-sm font-medium text-gray-700 mb-2">
//                     <FaUser className="inline mr-2" />
//                     Nombre del Encargado
//                   </label>
//                   <input
//                     type="text"
//                     name="nombre_encargado"
//                     value={formData.nombre_encargado}
//                     onChange={handleChange}
//                     disabled={isReadOnly}
//                     className={`w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
//                       isReadOnly ? 'bg-gray-50' : ''
//                     }`}
//                     placeholder="Ej: Juan Pérez"
//                   />
//                 </div>

//                 {/* Número de documento */}
//                 <div>
//                   <label className="block text-sm font-medium text-gray-700 mb-2">
//                     <FaIdCard className="inline mr-2" />
//                     Número de Documento *
//                   </label>
//                   <input
//                     type="text"
//                     name="numero_documento"
//                     value={formData.numero_documento}
//                     onChange={handleChange}
//                     disabled={isReadOnly}
//                     className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
//                       errors.numero_documento ? 'border-red-500' : 'border-gray-300'
//                     } ${isReadOnly ? 'bg-gray-50' : ''}`}
//                     placeholder="Ej: 900123456-1"
//                   />
//                   {errors.numero_documento && (
//                     <p className="text-red-500 text-sm mt-1">{errors.numero_documento}</p>
//                   )}
//                 </div>

//                 {/* Teléfono */}
//                 <div>
//                   <label className="block text-sm font-medium text-gray-700 mb-2">
//                     <FaPhone className="inline mr-2" />
//                     Teléfono
//                   </label>
//                   <input
//                     type="text"
//                     name="telefono"
//                     value={formData.telefono}
//                     onChange={handleChange}
//                     disabled={isReadOnly}
//                     className={`w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
//                       isReadOnly ? 'bg-gray-50' : ''
//                     }`}
//                     placeholder="Ej: +57 300 123 4567"
//                   />
//                 </div>

//                 {/* Email */}
//                 <div className="md:col-span-2">
//                   <label className="block text-sm font-medium text-gray-700 mb-2">
//                     <FaEnvelope className="inline mr-2" />
//                     Email
//                   </label>
//                   <input
//                     type="email"
//                     name="email"
//                     value={formData.email}
//                     onChange={handleChange}
//                     disabled={isReadOnly}
//                     className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
//                       errors.email ? 'border-red-500' : 'border-gray-300'
//                     } ${isReadOnly ? 'bg-gray-50' : ''}`}
//                     placeholder="Ej: contacto@empresa.com"
//                   />
//                   {errors.email && (
//                     <p className="text-red-500 text-sm mt-1">{errors.email}</p>
//                   )}
//                 </div>

//                 {/* Dirección */}
//                 <div className="md:col-span-2">
//                   <label className="block text-sm font-medium text-gray-700 mb-2">
//                     <FaMapMarkerAlt className="inline mr-2" />
//                     Dirección
//                   </label>
//                   <textarea
//                     name="direccion"
//                     value={formData.direccion}
//                     onChange={handleChange}
//                     disabled={isReadOnly}
//                     rows={3}
//                     className={`w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
//                       isReadOnly ? 'bg-gray-50' : ''
//                     }`}
//                     placeholder="Ej: Calle 123 #45-67, Bogotá, Colombia"
//                   />
//                 </div>
//               </div>
//             </form>
//           ) : activeTab === 'facturas' ? (
//             /* Tabla de facturas */
//             <div>
//               <h3 className="text-lg font-semibold mb-4">Facturas del Cliente</h3>
//               {clienteConFacturas?.facturas?.length > 0 ? (
//                 <div className="overflow-x-auto">
//                   <table className="w-full border border-gray-200 rounded-lg">
//                     <thead className="bg-gray-50">
//                       <tr>
//                         <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Número</th>
//                         <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Fecha</th>
//                         <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Total</th>
//                         <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Estado</th>
//                       </tr>
//                     </thead>
//                     <tbody className="divide-y divide-gray-200">
//                       {clienteConFacturas.facturas.map((factura) => (
//                         <tr key={factura.id} className="hover:bg-gray-50">
//                           <td className="px-4 py-2 text-sm">{factura.numero}</td>
//                           <td className="px-4 py-2 text-sm">{new Date(factura.fecha_emision).toLocaleDateString()}</td>
//                           <td className="px-4 py-2 text-sm font-medium">
//                             {formatoPesoColombiano(factura.total)}
//                           </td>
//                           <td className="px-4 py-2">
//                             <span className={`px-2 py-1 text-xs rounded-full ${
//                               factura.estado === 'pagada' ? 'bg-green-100 text-green-800' :
//                               factura.estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' :
//                               'bg-red-100 text-red-800'
//                             }`}>
//                               {factura.estado}
//                             </span>
//                           </td>
//                         </tr>
//                       ))}
//                     </tbody>
//                   </table>
//                 </div>
//               ) : (
//                 <div className="text-center py-8 text-gray-500">
//                   <FaFileInvoiceDollar size={48} className="mx-auto mb-4 text-gray-300" />
//                   <p>No hay facturas registradas para este cliente</p>
//                 </div>
//               )}
//             </div>
//           ) : activeTab === 'pagos' ? (
//             /* Tabla de pagos */
//             <div>
//               <h3 className="text-lg font-semibold mb-4">Pagos del Cliente</h3>
//               {clienteConPagos?.pagos?.length > 0 ? (
//                 <div className="overflow-x-auto">
//                   <table className="w-full border border-gray-200 rounded-lg">
//                     <thead className="bg-gray-50">
//                       <tr>
//                         <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Fecha</th>
//                         <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Factura</th>
//                         <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Monto</th>
//                         <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Método</th>
//                         <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Observaciones</th>
//                       </tr>
//                     </thead>
//                     <tbody className="divide-y divide-gray-200">
//                       {clienteConPagos.pagos.map((pago) => (
//                         <tr key={pago.id} className="hover:bg-gray-50">
//                           <td className="px-4 py-2 text-sm">{new Date(pago.fecha_pago).toLocaleDateString()}</td>
//                           <td className="px-4 py-2 text-sm">{pago.factura_numero}</td>
//                           <td className="px-4 py-2 text-sm font-medium text-green-600">
//                             {formatoPesoColombiano(pago.monto)}
//                           </td>
//                           <td className="px-4 py-2 text-sm">{pago.metodo_pago}</td>
//                           <td className="px-4 py-2 text-sm">{pago.observaciones || '-'}</td>
//                         </tr>
//                       ))}
//                     </tbody>
//                   </table>
//                 </div>
//               ) : (
//                 <div className="text-center py-8 text-gray-500">
//                   <FaMoneyBillWave size={48} className="mx-auto mb-4 text-gray-300" />
//                   <p>No hay pagos registrados para este cliente</p>
//                 </div>
//               )}
//             </div>
//           ) : (
//             /* Estadísticas del cliente */
//             <div>
//               <h3 className="text-lg font-semibold mb-4">Estadísticas del Cliente</h3>
//               {clienteEstadisticas ? (
//                 <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
//                   <div className="bg-blue-50 p-4 rounded-lg">
//                     <div className="text-2xl font-bold text-blue-600">
//                       {clienteEstadisticas.total_facturas}
//                     </div>
//                     <div className="text-sm text-blue-800">Total Facturas</div>
//                   </div>
//                   <div className="bg-green-50 p-4 rounded-lg">
//                     <div className="text-2xl font-bold text-green-600">
//                       {formatoPesoColombiano(clienteEstadisticas.total_facturado)}
//                     </div>
//                     <div className="text-sm text-green-800">Total Facturado</div>
//                   </div>
//                   <div className="bg-purple-50 p-4 rounded-lg">
//                     <div className="text-2xl font-bold text-purple-600">
//                       {formatoPesoColombiano(clienteEstadisticas.total_pagado)}
//                     </div>
//                     <div className="text-sm text-purple-800">Total Pagado</div>
//                   </div>
//                   <div className="bg-yellow-50 p-4 rounded-lg">
//                     <div className="text-2xl font-bold text-yellow-600">
//                       {formatoPesoColombiano(clienteEstadisticas.saldo_pendiente)}
//                     </div>
//                     <div className="text-sm text-yellow-800">Saldo Pendiente</div>
//                   </div>
//                 </div>
//               ) : (
//                 <div className="text-center py-8 text-gray-500">
//                   <FaChartLine size={48} className="mx-auto mb-4 text-gray-300" />
//                   <p>No hay estadísticas disponibles</p>
//                 </div>
//               )}
//             </div>
//           )}
//         </div>

//         {/* Footer */}
//         {activeTab === 'info' && (
//           <div className="bg-gray-50 px-6 py-4 flex justify-end gap-3">
//             <button
//               type="button"
//               onClick={onClose}
//               className="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
//             >
//               {mode === 'view' ? 'Cerrar' : 'Cancelar'}
//             </button>
            
//             {!isReadOnly && (
//               <button
//                 type="submit"
//                 disabled={loading}
//                 onClick={handleSubmit}
//                 className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
//               >
//                 {loading ? (
//                   <>
//                     <FaSpinner className="animate-spin" size={14} />
//                     Guardando...
//                   </>
//                 ) : (
//                   <>
//                     <FaSave size={14} />
//                     {mode === 'edit' ? 'Actualizar' : 'Crear'} Cliente
//                   </>
//                 )}
//               </button>
//             )}
//           </div>
//         )}
//       </div>
//     </div>
//   );
// };