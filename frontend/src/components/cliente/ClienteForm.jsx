import { useEffect } from 'react';
import { FaTimes, FaBuilding, FaUser, FaPhone, FaEnvelope, FaMapMarkerAlt, FaIdCard } from 'react-icons/fa';

export const ClienteForm = ({ 
    setShowForm, 
    eventToast, 
    onSubmit, 
    form, 
    setForm, 
    handleChange,
    createCliente,
    isCreating, 
    createError,
    updateCliente,
    updateError,
    editingCliente,
    setEditingCliente }) => {

    useEffect(() => {
        if (editingCliente) {
          setForm(editingCliente);
        } else {
          setForm({
            nombre_encargado: "",
            nombre_empresa: "",
            numero_documento: "",
            direccion: "",
            telefono: "",
            email: "",
            tipo: "",
            estado: ""
          });
        }
      }, [editingCliente, setForm]);

    const handleSubmit = (e) => {
        e.preventDefault();

        if(editingCliente){
            updateCliente({ id: editingCliente.id_clientes, data: form }, {
                onSuccess: () => {
                    onSubmit();
                    setShowForm(false);
                    setEditingCliente(null);
                    eventToast("Cliente actualizado exitosamente", "success");
                }
            });
        } else {
            createCliente(form, {
                onSuccess: () => {
                    onSubmit();
                    setShowForm(false);
                    eventToast("Cliente creado exitosamente", "success");
                }
            });
            
        }
    }

    return (
        <div onClick={() => setShowForm(false)}
            className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto"
                onClick={e => e.stopPropagation()}>
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200">
                    <div className="flex items-center gap-3">
                        <div className="bg-blue-100 p-2 rounded-lg">
                            <FaUser className="text-blue-600" size={20} />
                        </div>
                        <div>
                            <h2 className="text-xl font-semibold text-gray-900">
                                {editingCliente  ? 'Editar cliente' : 'Nuevo cliente'}
                            </h2>
                            <p className="text-sm text-gray-600">
                                {editingCliente  ? 'Modifica la información del cliente' : 'Ingresa los datos del nuevo cliente'}
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={() => setShowForm(false)}
                        className="p-2 hover:bg-gray-100 cursor-pointer rounded-lg transition-colors"
                    >
                        <FaTimes className="text-gray-500" size={20} />
                    </button>
                </div>

                <form className="p-6" onSubmit={handleSubmit}>
                    {(createError || updateError) && (
                        <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p className="text-red-600 text-sm">{createError?.message || updateError?.message}</p>
                        </div>
                    )}

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {/* Form */}
                        {/* Nombre del encargado */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaUser className="inline mr-2" size={14} />
                                Nombre del encargado <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="nombre_encargado"
                                value={form.nombre_encargado || ""}
                                onChange={handleChange}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Ej: Carlos Rodríguez"
                            />
                        </div>

                        {/* Nombre de la empresa */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaUser className="inline mr-2" size={14} />
                                Nombre de la empresa <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="nombre_empresa"
                                value={form.nombre_empresa || ""}
                                onChange={handleChange}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Ej: Carlos Rodríguez"
                            />
                        </div>

                        {/* Numero documento */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaBuilding className="inline mr-2" size={14} />
                                Numero documento <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="numero_documento"
                                value={form.numero_documento || ""}
                                onChange={handleChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${createError ? 'border-red-300' : 'border-gray-300'
                                    }`}
                                placeholder="Ej: Químicos Industriales S.A.S"
                            />
                            {(createError || updateError) && (
                                <p className="text-red-500 text-xs mt-1">{createError?.message || updateError?.message}</p>
                            )}
                        </div>

                        {/* Direccion */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaPhone className="inline mr-2" size={14} />
                                Direccion <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="direccion"
                                value={form.direccion || ""}
                                onChange={handleChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${createError ? 'border-red-300' : 'border-gray-300'
                                    }`}
                                placeholder="Ej: Calle 123 #45-67"
                            />
                            {(createError || updateError) && (
                                <p className="text-red-500 text-xs mt-1">{createError?.message || updateError?.message}</p>
                            )}
                        </div>

                        {/* Telefono */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaPhone className="inline mr-2" size={14} />
                                Telefono <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="telefono"
                                value={form.telefono || ""}
                                onChange={handleChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${createError ? 'border-red-300' : 'border-gray-300'
                                    }`}
                                placeholder="Ej: Calle 123 #45-67"
                            />
                            {(createError || updateError) && (
                                <p className="text-red-500 text-xs mt-1">{createError?.message || updateError?.message}</p>
                            )}
                        </div>

                        {/* Email */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaEnvelope className="inline mr-2" size={14} />
                                Email <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="email"
                                value={form.email || ""}
                                onChange={handleChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${createError ? 'border-red-300' : 'border-gray-300'
                                    }`}
                                placeholder="Ej: correo@ejemplo.com"
                            />
                            {(createError || updateError) && (   
                                <p className="text-red-500 text-xs mt-1">{createError?.message || updateError?.message}</p>
                            )}
                        </div>

                        {/* Estado */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaEnvelope className="inline mr-2" size={14} />
                                Estado <span className='text-red-600'>*</span>
                            </label>
                            <select
                                name="estado"
                                value={form.estado || ""}
                                onChange={handleChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${createError ? 'border-red-300' : 'border-gray-300'
                                    }`}>
                                <option value="">Seleccione un estado</option>
                                <option value="1">ACTIVO</option>
                                <option value="2">INACTIVO</option>
                            </select>
                        </div>

                        {/* Tipo */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaEnvelope className="inline mr-2" size={14} />
                                Tipo <span className='text-red-600'>*</span>
                            </label>
                            <select
                                name="tipo"
                                value={form.tipo || ""}
                                onChange={handleChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${createError ? 'border-red-300' : 'border-gray-300'
                                    }`}>
                                <option value="">Seleccione un tipo</option>
                                <option value="2">NATURAL</option>
                                <option value="1">EMPRESA</option>
                            </select>
                        </div>
                    </div>
                    <button
                        type="submit"
                        disabled={!form.nombre_encargado || !form.nombre_empresa || !form.numero_documento || !form.direccion || !form.telefono || !form.email || isCreating || !form.tipo || !form.estado  }
                        className="disabled:opacity-50 w-full cursor-pointer mt-4 py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 transition"
                        >
                        {
                        isCreating
                            ? "Guardando..."
                            : editingCliente
                            ? "Actualizar Cliente"
                            : "Crear Cliente"
                        }
                    </button>
                </form>
            </div>
        </div>
    )
}