// // frontend/src/components/ItemProveedorModal.jsx
// import React, { useState, useEffect } from 'react';
// import { FaTimes, FaBox, FaTag, FaDollarSign, FaAlignLeft, FaBuilding, FaBarcode } from 'react-icons/fa';

// export const ItemProveedorModal = ({ itemProveedor, proveedores = [], onSubmit, onClose }) => {
//     const [formData, setFormData] = useState({
//         nombre_item: '',
//         codigo_item: '',
//         descripcion: '',
//         precio: '',
//         cantidad: '',
//         categoria: '',
//         proveedor_id: ''
//     });
//     const [errors, setErrors] = useState({});
//     const [loading, setLoading] = useState(false);

//     useEffect(() => {
//         if (itemProveedor) {
//             setFormData({
//                 nombre_item: itemProveedor.nombre_item || '',
//                 codigo_item: itemProveedor.codigo_item || '',
//                 descripcion: itemProveedor.descripcion || '',
//                 precio: itemProveedor.precio || '',
//                 cantidad: itemProveedor.cantidad || '',
//                 categoria: itemProveedor.categoria || '',
//                 proveedor_id: itemProveedor.proveedor_id || ''
//             });
//         }
//     }, [itemProveedor]);

//     const handleChange = (e) => {
//         const { name, value } = e.target;
//         setFormData(prev => ({ ...prev, [name]: value }));
        
//         if (errors[name]) {
//             setErrors(prev => ({ ...prev, [name]: '' }));
//         }
//     };

//     const validateForm = () => {
//         const newErrors = {};

//         if (!formData.nombre_item.trim()) {
//             newErrors.nombre_item = 'El nombre del item es requerido';
//         }

//         if (!formData.proveedor_id) {
//             newErrors.proveedor_id = 'Debe seleccionar un proveedor';
//         }

//         if (formData.precio && (isNaN(formData.precio) || parseFloat(formData.precio) < 0)) {
//             newErrors.precio = 'El precio debe ser un número válido mayor o igual a 0';
//         }

//         if (formData.cantidad && (isNaN(formData.cantidad) || parseInt(formData.cantidad) < 0)) {
//             newErrors.cantidad = 'La cantidad debe ser un número entero mayor o igual a 0';
//         }

//         setErrors(newErrors);
//         return Object.keys(newErrors).length === 0;
//     };

//     const handleSubmit = async (e) => {
//         e.preventDefault();
        
//         if (!validateForm()) {
//             return;
//         }

//         setLoading(true);
//         try {
//             // Convertir tipos de datos
//             const processedData = {
//                 ...formData,
//                 precio: formData.precio ? parseFloat(formData.precio) : 0,
//                 cantidad: formData.cantidad ? parseInt(formData.cantidad) : 0,
//                 proveedor_id: parseInt(formData.proveedor_id)
//             };

//             const result = await onSubmit(processedData);
//             if (!result.success) {
//                 if (typeof result.error === 'string') {
//                     setErrors({ general: result.error });
//                 } else {
//                     setErrors(result.error || { general: 'Error al guardar el item' });
//                 }
//             }
//         } catch (error) {
//             setErrors({ general: `Error inesperado: ${error}` });
//         } finally {
//             setLoading(false);
//         }
//     };

//     return (
//         <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
//             <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
//                 {/* Header */}
//                 <div className="flex items-center justify-between p-6 border-b">
//                     <div className="flex items-center gap-3">
//                         <div className="bg-green-100 p-2 rounded-lg">
//                             <FaBox className="text-green-600" size={20} />
//                         </div>
//                         <div>
//                             <h2 className="text-xl font-semibold text-gray-900">
//                                 {itemProveedor ? 'Editar Item-Proveedor' : 'Nuevo Item-Proveedor'}
//                             </h2>
//                             <p className="text-sm text-gray-600">
//                                 {itemProveedor ? 'Modifica la información del item' : 'Ingresa los datos del nuevo item'}
//                             </p>
//                         </div>
//                     </div>
//                     <button
//                         onClick={onClose}
//                         className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
//                     >
//                         <FaTimes className="text-gray-500" size={20} />
//                     </button>
//                 </div>

//                 {/* Form */}
//                 <form onSubmit={handleSubmit} className="p-6">
//                     {errors.general && (
//                         <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
//                             <p className="text-red-600 text-sm">{errors.general}</p>
//                         </div>
//                     )}

//                     <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
//                         {/* Proveedor */}
//                         <div className="md:col-span-2">
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaBuilding className="inline mr-2" size={14} />
//                                 Proveedor *
//                             </label>
//                             <select
//                                 name="proveedor_id"
//                                 value={formData.proveedor_id}
//                                 onChange={handleChange}
//                                 className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent ${
//                                     errors.proveedor_id ? 'border-red-300' : 'border-gray-300'
//                                 }`}
//                             >
//                                 <option value="">Seleccionar proveedor...</option>
//                                 {proveedores.map(proveedor => (
//                                     <option key={proveedor.id} value={proveedor.id}>
//                                         {proveedor.nombre_empresa}
//                                     </option>
//                                 ))}
//                             </select>
//                             {errors.proveedor_id && (
//                                 <p className="text-red-500 text-xs mt-1">{errors.proveedor_id}</p>
//                             )}
//                         </div>

//                         {/* Nombre del item */}
//                         <div>
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaTag className="inline mr-2" size={14} />
//                                 Nombre del Item *
//                             </label>
//                             <input
//                                 type="text"
//                                 name="nombre_item"
//                                 value={formData.nombre_item}
//                                 onChange={handleChange}
//                                 className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent ${
//                                     errors.nombre_item ? 'border-red-300' : 'border-gray-300'
//                                 }`}
//                                 placeholder="Ej: Tornillo hexagonal M8"
//                             />
//                             {errors.nombre_item && (
//                                 <p className="text-red-500 text-xs mt-1">{errors.nombre_item}</p>
//                             )}
//                         </div>

//                         {/* Código del item */}
//                         <div>
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaBarcode className="inline mr-2" size={14} />
//                                 Código del Item
//                             </label>
//                             <input
//                                 type="text"
//                                 name="codigo_item"
//                                 value={formData.codigo_item}
//                                 onChange={handleChange}
//                                 className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
//                                 placeholder="Ej: TH-M8-001"
//                             />
//                         </div>

//                         {/* Precio */}
//                         <div>
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaDollarSign className="inline mr-2" size={14} />
//                                 Precio
//                             </label>
//                             <input
//                                 type="number"
//                                 name="precio"
//                                 value={formData.precio}
//                                 onChange={handleChange}
//                                 step="0.01"
//                                 min="0"
//                                 className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent ${
//                                     errors.precio ? 'border-red-300' : 'border-gray-300'
//                                 }`}
//                                 placeholder="0.00"
//                             />
//                             {errors.precio && (
//                                 <p className="text-red-500 text-xs mt-1">{errors.precio}</p>
//                             )}
//                         </div>

//                         {/* Cantidad */}
//                         <div>
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaBox className="inline mr-2" size={14} />
//                                 Cantidad
//                             </label>
//                             <input
//                                 type="number"
//                                 name="cantidad"
//                                 value={formData.cantidad}
//                                 onChange={handleChange}
//                                 min="0"
//                                 className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent ${
//                                     errors.cantidad ? 'border-red-300' : 'border-gray-300'
//                                 }`}
//                                 placeholder="0"
//                             />
//                             {errors.cantidad && (
//                                 <p className="text-red-500 text-xs mt-1">{errors.cantidad}</p>
//                             )}
//                         </div>

//                         {/* Categoría */}
//                         <div className="md:col-span-2">
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaTag className="inline mr-2" size={14} />
//                                 Categoría
//                             </label>
//                             <input
//                                 type="text"
//                                 name="categoria"
//                                 value={formData.categoria}
//                                 onChange={handleChange}
//                                 className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
//                                 placeholder="Ej: Ferretería, Químicos, Herramientas..."
//                             />
//                         </div>

//                         {/* Descripción */}
//                         <div className="md:col-span-2">
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaAlignLeft className="inline mr-2" size={14} />
//                                 Descripción
//                             </label>
//                             <textarea
//                                 name="descripcion"
//                                 value={formData.descripcion}
//                                 onChange={handleChange}
//                                 rows="3"
//                                 className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
//                                 placeholder="Descripción detallada del item..."
//                             />
//                         </div>
//                     </div>

//                     {/* Buttons */}
//                     <div className="flex justify-end gap-3 mt-6 pt-4 border-t">
//                         <button
//                             type="button"
//                             onClick={onClose}
//                             className="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
//                             disabled={loading}
//                         >
//                             Cancelar
//                         </button>
//                         <button
//                             type="submit"
//                             disabled={loading}
//                             className="px-6 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
//                         >
//                             {loading ? (
//                                 <>
//                                     <div className="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent" />
//                                     Guardando...
//                                 </>
//                             ) : (
//                                 <>
//                                     {itemProveedor ? 'Actualizar' : 'Crear'} Item
//                                 </>
//                             )}
//                         </button>
//                     </div>
//                 </form>
//             </div>
//         </div>
//     );
// };