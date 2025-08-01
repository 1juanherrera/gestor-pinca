// import React, { useState, useEffect } from 'react';
// import { FaTimes, FaBuilding, FaUser, FaPhone, FaEnvelope, FaMapMarkerAlt, FaIdCard } from 'react-icons/fa';

// export const ProveedorModal = ({ proveedor, onSubmit, onClose }) => {
//     const [formData, setFormData] = useState({
//         nombre_empresa: '',
//         nombre_encargado: '',
//         numero_documento: '',
//         direccion: '',
//         telefono: '',
//         email: ''
//     });
//     const [errors, setErrors] = useState({});
//     const [loading, setLoading] = useState(false);

//     useEffect(() => {
//         if (proveedor) {
//             setFormData({
//                 nombre_empresa: proveedor.nombre_empresa || '',
//                 nombre_encargado: proveedor.nombre_encargado || '',
//                 numero_documento: proveedor.numero_documento || '',
//                 direccion: proveedor.direccion || '',
//                 telefono: proveedor.telefono || '',
//                 email: proveedor.email || ''
//             });
//         }
//     }, [proveedor]);

//     const handleChange = (e) => {
//         const { name, value } = e.target;
//         setFormData(prev => ({ ...prev, [name]: value }));
        
//         // Limpiar error del campo cuando el usuario comience a escribir
//         if (errors[name]) {
//             setErrors(prev => ({ ...prev, [name]: '' }));
//         }
//     };

//     const validateForm = () => {
//         const newErrors = {};

//         if (!formData.nombre_empresa.trim()) {
//             newErrors.nombre_empresa = 'El nombre de la empresa es requerido';
//         }

//         if (formData.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
//             newErrors.email = 'El formato del email no es válido';
//         }

//         if (formData.telefono && !/^[+]?[\s\-()]*([0-9][\s\-()]*){6,20}$/.test(formData.telefono)) {
//             newErrors.telefono = 'El formato del teléfono no es válido';
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
//             const result = await onSubmit(formData);
//             if (!result.success) {
//                 if (typeof result.error === 'string') {
//                     setErrors({ general: result.error });
//                 } else {
//                     setErrors(result.error || { general: 'Error al guardar el proveedor' });
//                 }
//             }
//         } catch (error) {
//             setErrors({ general: `Error inesperado al guardar el proveedor ${error}` });
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
//                         <div className="bg-blue-100 p-2 rounded-lg">
//                             <FaBuilding className="text-blue-600" size={20} />
//                         </div>
//                         <div>
//                             <h2 className="text-xl font-semibold text-gray-900">
//                                 {proveedor ? 'Editar Proveedor' : 'Nuevo Proveedor'}
//                             </h2>
//                             <p className="text-sm text-gray-600">
//                                 {proveedor ? 'Modifica la información del proveedor' : 'Ingresa los datos del nuevo proveedor'}
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
//                         {/* Nombre de la empresa */}
//                         <div className="md:col-span-2">
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaBuilding className="inline mr-2" size={14} />
//                                 Nombre de la Empresa <span className='text-red-600'>*</span>
//                             </label>
//                             <input
//                                 type="text"
//                                 name="nombre_empresa"
//                                 value={formData.nombre_empresa}
//                                 onChange={handleChange}
//                                 className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
//                                     errors.nombre_empresa ? 'border-red-300' : 'border-gray-300'
//                                 }`}
//                                 placeholder="Ej: Químicos Industriales S.A.S"
//                             />
//                             {errors.nombre_empresa && (
//                                 <p className="text-red-500 text-xs mt-1">{errors.nombre_empresa}</p>
//                             )}
//                         </div>

//                         {/* Nombre del encargado */}
//                         <div>
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaUser className="inline mr-2" size={14} />
//                                 Nombre del Encargado <span className='text-red-600'>*</span>
//                             </label>
//                             <input
//                                 type="text"
//                                 name="nombre_encargado"
//                                 value={formData.nombre_encargado}
//                                 onChange={handleChange}
//                                 className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
//                                 placeholder="Ej: Carlos Rodríguez"
//                             />
//                         </div>

//                         {/* Número de documento */}
//                         <div>
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaIdCard className="inline mr-2" size={14} />
//                                 Número de Documento
//                             </label>
//                             <input
//                                 type="text"
//                                 name="numero_documento"
//                                 value={formData.numero_documento}
//                                 onChange={handleChange}
//                                 className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
//                                 placeholder="Ej: 900123456-7"
//                             />
//                         </div>

//                         {/* Teléfono */}
//                         <div>
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaPhone className="inline mr-2" size={14} />
//                                 Teléfono
//                             </label>
//                             <input
//                                 type="tel"
//                                 name="telefono"
//                                 value={formData.telefono}
//                                 onChange={handleChange}
//                                 className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
//                                     errors.telefono ? 'border-red-300' : 'border-gray-300'
//                                 }`}
//                                 placeholder="Ej: +57 1 234-5678"
//                             />
//                             {errors.telefono && (
//                                 <p className="text-red-500 text-xs mt-1">{errors.telefono}</p>
//                             )}
//                         </div>

//                         {/* Email */}
//                         <div>
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaEnvelope className="inline mr-2" size={14} />
//                                 Email
//                             </label>
//                             <input
//                                 type="email"
//                                 name="email"
//                                 value={formData.email}
//                                 onChange={handleChange}
//                                 className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
//                                     errors.email ? 'border-red-300' : 'border-gray-300'
//                                 }`}
//                                 placeholder="Ej: ventas@empresa.com"
//                             />
//                             {errors.email && (
//                                 <p className="text-red-500 text-xs mt-1">{errors.email}</p>
//                             )}
//                         </div>

//                         {/* Dirección */}
//                         <div className="md:col-span-2">
//                             <label className="block text-sm font-medium text-gray-700 mb-1">
//                                 <FaMapMarkerAlt className="inline mr-2" size={14} />
//                                 Dirección
//                             </label>
//                             <textarea
//                                 name="direccion"
//                                 value={formData.direccion}
//                                 onChange={handleChange}
//                                 rows="3"
//                                 className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
//                                 placeholder="Ej: Calle 45 #12-34, Zona Industrial, Bogotá D.C."
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
//                             className="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
//                         >
//                             {loading ? (
//                                 <>
//                                     <div className="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent" />
//                                     Guardando...
//                                 </>
//                             ) : (
//                                 <>
//                                     {proveedor ? 'Actualizar' : 'Crear'} Proveedor
//                                 </>
//                             )}
//                         </button>
//                     </div>
//                 </form>
//             </div>
//         </div>
//     );
// };