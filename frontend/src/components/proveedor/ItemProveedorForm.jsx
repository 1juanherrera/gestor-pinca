import { useEffect } from 'react';
import { FaTimes, FaBuilding, FaUser, FaPhone, FaEnvelope, FaMapMarkerAlt, FaIdCard } from 'react-icons/fa';

export const ItemProveedorForm = ({
    setShowItemCreate,
    createItem,
    isCreatingItem,
    createItemError,
    refreshData,
    updateItem,
    proveedoresData,
    handleItemChange,
    setFormItem,
    formItem,
    editingItem,
    setEditingItem }) => {

    useEffect(() => {
        if (editingItem) {
            setFormItem(editingItem);
        } else {
            setFormItem({
                nombre: "",
                codigo: "",
                tipo: "",
                unidad_empaque: "",
                precio_unitario: "",
                precio_con_iva: "",
                disponible: "",
                descripcion: ""
            });
        }
    }, [editingItem, setFormItem]);

    const handleSubmit = (e) => {
        e.preventDefault();

        if (editingItem) {
            updateItem({ id: editingItem.id_item_proveedor, data: formItem }, {
                onSuccess: () => {
                    refreshData();
                    setShowItemCreate(false);
                    setEditingItem(null);
                }
            });
        } else {
            createItem(formItem, {
                onSuccess: () => {
                    refreshData();
                    setShowItemCreate(false);
                }
            });
        }
    }

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" 
            onClick={() => setShowItemCreate(false)}>
            <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto"
                onClick={e => e.stopPropagation()}>
                <div className="flex items-center justify-between p-6 border-b border-gray-200">
                    <div className="flex items-center gap-3">
                        <div className="bg-blue-100 p-2 rounded-lg">
                            <FaBuilding className="text-blue-600" size={20} />
                        </div>
                        <div>
                            <h2 className="text-xl font-semibold text-gray-900">
                                {editingItem ? 'Editar Producto' : 'Nuevo Producto'}
                            </h2>
                            <p className="text-sm text-gray-600">
                                {editingItem ? 'Modifica la información del producto' : 'Ingresa los datos del nuevo producto'}
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={() => setShowItemCreate(false)}
                        className="p-2 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                    >
                        <FaTimes className="text-gray-500" size={20} />
                    </button>
                </div>
                <form onSubmit={handleSubmit} className="bg-white p-6 rounded-xl shadow space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            <FaUser className="inline mr-2" size={14} />
                            Nombre <span className='text-red-600'>*</span>
                        </label>
                        <input
                            type="text"
                            name="nombre"
                            value={formItem.nombre}
                            onChange={handleItemChange}
                            className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                createItemError ? 'border-red-300' : 'border-gray-300'
                            }`} />
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaBuilding className="inline mr-2" size={14} />
                                Codigo <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="codigo"
                                value={formItem.codigo}
                                onChange={handleItemChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${createItemError ? 'border-red-300' : 'border-gray-300'}`} />
                                {createItemError && (
                                    <p className="text-red-500 text-xs mt-1">{createItemError.message}</p>
                                )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaUser className="inline mr-2" size={14} />
                                Tipo <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="tipo"
                                value={formItem.tipo}
                                onChange={handleItemChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                    createItemError ? 'border-red-300' : 'border-gray-300'
                                }`} />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaUser className="inline mr-2" size={14} />
                                Unidad <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="unidad_empaque"
                                value={formItem.unidad_empaque}
                                onChange={handleItemChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                    createItemError ? 'border-red-300' : 'border-gray-300'
                                }`} />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaUser className="inline mr-2" size={14} />
                                Precio Unitario <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="precio_unitario"
                                value={formItem.precio_unitario}
                                onChange={handleItemChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                    createItemError ? 'border-red-300' : 'border-gray-300'
                                }`} />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaUser className="inline mr-2" size={14} />
                                Precio con Iva <span className='text-red-600'>*</span>
                            </label>
                            <input
                                type="text"
                                name="precio_con_iva"
                                value={formItem.precio_con_iva}
                                onChange={handleItemChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                    createItemError ? 'border-red-300' : 'border-gray-300'
                                }`} />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaUser className="inline mr-2" size={14} />
                                Disponible <span className='text-red-600'>*</span>
                            </label>
                            <select name="disponible" value={formItem.disponible}
                                onChange={handleItemChange}
                                className={`text-black w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                    createItemError ? 'border-red-300' : 'border-gray-300'
                                }`}>
                                <option value="">Selecciona una opcion...</option>
                                <option value="1">Disponible</option>
                                <option value="0">No disponible</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaUser className="inline mr-2" size={14} />
                                Proveedor <span className='text-red-600'>*</span>
                            </label>
                            <select 
                                name="proveedor_id" 
                                value={formItem.proveedor_id}
                                onChange={handleItemChange}
                                className={`text-black w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                    createItemError ? 'border-red-300' : 'border-gray-300'
                                }`}>
                                <option value="">Selecciona una opcion...</option>
                                {proveedoresData.map((prov) => (
                                    <option key={prov.id_proveedor} value={prov.id_proveedor}>
                                        {prov.nombre_empresa}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                <FaUser className="inline mr-2" size={14} />
                                Descripcion
                            </label>
                            <input
                                type="text"
                                name="descripcion"
                                value={formItem.descripcion}
                                onChange={handleItemChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent ${
                                    createItemError ? 'border-red-300' : 'border-gray-300'
                                }`}
                            />
                        </div>
                    </div>
                    <button
                        type="submit"
                        disabled={!formItem.nombre && !formItem.descripcion && !formItem.codigo && !formItem.tipo && !formItem.unidad_empaque && !formItem.precio_unitario && !formItem.precio_con_iva && !formItem.disponible && !formItem.proveedor_id}
                        className="w-full cursor-pointer py-2 px-4 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 transition"
                        >
                        {
                        isCreatingItem
                            ? "Guardando..."
                            : editingItem
                            ? "Actualizar Item"
                            : "Crear Item"
                        }
                    </button>
                    {createItemError && (
                        <p className="text-red-500">Error: {createItemError.message || "No se pudo guardar"}</p>
                    )}
                </form>
            </div>
        </div>
    )
}