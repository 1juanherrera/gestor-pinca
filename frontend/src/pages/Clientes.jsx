// import { useState } from 'react';
import {
    FaPlus,
    FaSearch,
    FaUsers,
    FaEye,
    FaEdit,
    FaTrash,
    FaBuilding,
    FaPhone,
    FaEnvelope,
} from 'react-icons/fa';
// import { ClienteModal } from '../components/cliente/ClienteModal';
// import { ClienteCard } from '../components/cliente/ClienteCard';
import { ClienteStats } from '../components/cliente/ClienteStats';
// import { DeleteConfirmModal } from '../components/cliente/DeleteConfirmModal';
import { useClientes } from "../hooks/useClientes";
import { Loader } from '../components/Loader';

export const Clientes = () => {

    const {
        data: clientes,
        isLoading,
        error,
        refreshData,

        createCliente,
        isCreating,
        createError,

        updateCliente,
        isUpdating,
        updateError,

        removeCliente,
        isDeleting,
        deleteError,
    } = useClientes();

    // const [searchTerm, setSearchTerm] = useState('');

    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">
            {/* Header */}
            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div className="flex items-center gap-2">
                    <FaUsers className="text-blue-600" size={25} />
                    <h1 className="text-xl font-bold text-gray-800">
                        Gestión de Clientes
                    </h1>
                </div>
                <div className="flex flex-col sm:flex-row gap-3 mb-4">
                    <button className="cursor-pointer flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <FaPlus size={16} />
                        Nuevo Cliente
                    </button>
                </div>
            </div>
            {/* Estadísticas */}
            <ClienteStats estadisticas={clientes} />

            {/* Filtros y búsqueda */}
            <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    {/* Búsqueda */}
                    <div className="relative flex-1 max-w-md">
                        <FaSearch className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={16} />
                        <input
                            type="text"
                            placeholder="Buscar por empresa, encargado, documento..."
                            className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                </div>
            </div>

            {/* Contenido principal */}
            {error && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div className="flex justify-between items-center">
                        <p className="text-red-800">{error}</p>
                    </div>
                </div>
            )}

            {isLoading ? (
                <Loader />
            )
                : clientes.length === 0 ? (
                    <div className="bg-white rounded-lg shadow-sm p-12 text-center">
                        <FaUsers size={64} className="text-gray-300 mx-auto mb-4" />
                        <h3 className="text-xl font-semibold text-gray-900 mb-2">
                            {searchTerm ? 'No se encontraron clientes' : 'No hay clientes registrados'}
                        </h3>
                        <p className="text-gray-600 mb-6">
                            {searchTerm
                                ? 'Intenta con otros términos de búsqueda'
                                : 'Comienza agregando tu primer cliente'}
                        </p>
                        {!searchTerm && (
                            <button className="inline-flex cursor-pointer items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <FaPlus size={16} />
                                Crear primer cliente
                            </button>
                        )}
                    </div>
                ) : (
                    <div className="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cliente
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contacto
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Documento
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {
                                        clientes.map((cliente, i) => (
                                            <tr key={i} className="hover:bg-gray-50">
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex items-center">
                                                        <div className="shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <FaBuilding className="text-blue-600" size={16} />
                                                        </div>
                                                        <div className="ml-4">
                                                            <div className="text-sm font-medium text-gray-900">
                                                                {cliente.nombre_empresa}
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {cliente.nombre_encargado}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="text-sm text-gray-900">
                                                        {cliente.telefono && (
                                                            <div className="flex items-center gap-1 mb-1">
                                                                <FaPhone size={12} className="text-gray-400" />
                                                                {cliente.telefono}
                                                            </div>
                                                        )}
                                                        {cliente.email && (
                                                            <div className="flex items-center gap-1">
                                                                <FaEnvelope size={12} className="text-gray-400" />
                                                                {cliente.email}
                                                            </div>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {cliente.numero_documento}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        Activo
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <button className="p-2 bg-gray-500 text-white hover:bg-gray-800 rounded-md transition-colors cursor-pointer" title="Editar"><FaEdit size={16} /></button>
                                                        <button className="p-2 bg-red-500 text-white hover:bg-red-800 rounded-md transition-colors cursor-pointer" title="Eliminar"><FaTrash size={16} /></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    }
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

        </div>
    )
}

//         {/* Modales */}
//         {isModalOpen && (
//           <ClienteModal
//             isOpen={isModalOpen}
//             onClose={cerrarModal}
//             mode={modalMode}
//             cliente={selectedClienteLocal}
//             onSave={modalMode === 'edit' ? actualizarCliente : crearCliente}
//           />
//         )}

//         {showDeleteModal && (
//           <DeleteConfirmModal
//             isOpen={showDeleteModal}
//             onConfirm={handleDeleteConfirm}
//             onCancel={handleDeleteCancel}
//             itemName={clienteToDelete?.nombre_empresa}
//             itemType="cliente"
//           />
//         )}
//       </div>
//     </div>
//   );
// };