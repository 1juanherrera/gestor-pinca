
import { useState } from "react";
import { FaTrash, FaEdit } from "react-icons/fa";

export const Table = ({ products = [], isLoading = false, error = null, onEdit, onDelete }) => {

    const [showConfirmModal, setShowConfirmModal] = useState(false);
    const [itemToDelete, setItemToDelete] = useState(null);


    // Función para obtener el nombre del producto
    const getNombre = (item) => item.nombre_item_general || item.nombre || '-';

    // Función para obtener el código del producto
    const getCodigo = (item) => item.codigo_item_general || item.codigo || '-';

    // Función para obtener el tipo del producto
    const getTipo = (item) => (item.nombre_tipo || item.tipo || '-').toUpperCase();

    // Función para obtener la categoría
    const getCategoria = (item) => item.categoria || 'MATERIA PRIMA';

    // Función para asignar clases según el tipo
    const handleType = (item) => {
        const tipo = getTipo(item);
        switch (tipo) {
            case 'PRODUCTO':
                return 'bg-blue-100 text-blue-700';
            case 'MATERIA PRIMA':
                return 'bg-purple-100 text-purple-700';
            case 'INSUMO':
                return 'bg-yellow-100 text-yellow-700';
            default:
                return 'bg-gray-100 text-gray-700';
        }
    };

    // Mostrar columna Costo Unitario solo si algún producto tiene costo_unitario definido
    const shouldShowCostoUnitario = products.some(producto => producto.costo_unitario !== undefined && producto.costo_unitario !== null);

    const handleDeleteClick = (producto) => {
        setItemToDelete(producto);
        setShowConfirmModal(true);
    };

    const handleConfirmDelete = () => {
        if (itemToDelete && onDelete) {
            onDelete(itemToDelete.id);
        }
        setShowConfirmModal(false);
        setItemToDelete(null);
    };

    const handleCancelDelete = () => {
        setShowConfirmModal(false);
        setItemToDelete(null);
    };

    if (isLoading) return <div>Cargando...</div>;
    if (error) return <div>Error: {error.message || error}</div>;

    return (
        <>
            <div className="overflow-hidden rounded-lg border border-gray-200">
                <div className="max-h-[70vh] overflow-y-auto">
                    <table className="w-full">
                        <thead className="sticky top-0 bg-gray-700 text-white uppercase">
                            <tr>
                                <th className="px-4 py-2 text-center text-xs font-medium w-15">ID</th>
                                <th className="px-4 py-2 text-center text-xs font-medium w-25">Codigo</th>
                                <th className="px-4 py-2 text-left text-xs font-medium">Nombre</th>
                                <th className="px-4 py-2 text-center text-xs font-medium w-32">Tipo</th>
                                <th className="px-4 py-2 text-center text-xs font-medium w-30">Categoría</th>
                                {shouldShowCostoUnitario && (
                                    <th className="px-4 py-2 text-center text-xs font-medium w-40">Costo Unit.</th>
                                )}
                                <th className="px-4 py-2 text-center text-xs font-medium w-32">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="bg-gray-100">
                            {products.map((producto, index) => (
                                <tr 
                                    key={producto.id || producto.codigo_item_general || producto.codigo || index} 
                                    className={`border-b border-gray-300 hover:bg-gray-200 transition-colors ${
                                        index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                    }`}
                                >
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {producto.id || '-'}
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {getCodigo(producto)}
                                        </span>
                                    </td>
                                    <td className="p-1 border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {getNombre(producto)}
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className={`px-3 py-1 block text-xs font-medium rounded-md ${handleType(producto)}`}>
                                            {getTipo(producto)}
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-semibold text-gray-900">
                                            {getCategoria(producto)}
                                        </span>
                                    </td>
                                    {shouldShowCostoUnitario && (
                                        <td className="p-1 text-left border border-gray-200">
                                            <span className="text-xs font-semibold text-emerald-800 pl-10">
                                                {producto.costo_unitario ?? '-'}
                                            </span>
                                        </td>
                                    )}
                                    <td className="p-1 py-1 border border-gray-200">
                                        <div className="flex justify-center gap-2">
                                            {onEdit && (
                                                <button 
                                                    onClick={() => onEdit(producto)}
                                                    className="p-2 text-white rounded-md transition-colors bg-gray-500 hover:bg-gray-800 cursor-pointer"
                                                    title="Editar"
                                                >
                                                    <FaEdit size={14} />
                                                </button>
                                            )}
                                            {onDelete && (
                                                <button 
                                                    onClick={() => handleDeleteClick(producto)}
                                                    className="p-2 bg-red-500 text-white hover:bg-red-800 rounded-md transition-colors cursor-pointer"
                                                    title="Eliminar"
                                                >
                                                    <FaTrash size={14} />
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modal de confirmación */}
            {showConfirmModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div className="bg-white rounded-lg shadow-xl max-w-md w-full">
                        <div className="p-6">
                            <div className="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                                <FaTrash className="w-6 h-6 text-red-600" />
                            </div>
                            <h3 className="text-lg font-medium text-gray-900 text-center mb-2">
                                Confirmar eliminación
                            </h3>
                            <p className="text-sm text-gray-500 text-center mb-6">
                                ¿Estás seguro de que quieres eliminar <strong>"{itemToDelete?.nombre}"</strong>?
                                <br />
                                <span className="text-red-600">Esta acción no se puede deshacer.</span>
                            </p>
                            <div className="flex gap-4">
                                <button
                                    onClick={handleCancelDelete}
                                    className="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                >
                                    Cancelar
                                </button>
                                <button
                                    onClick={handleConfirmDelete}
                                    className="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
};