// import { FaUserTag, FaPlus, FaSearch, FaEdit, FaTrash, FaBuilding, FaEnvelope, FaPhone, FaArrowLeft, FaBox } from "react-icons/fa";
// import { AiFillProduct } from "react-icons/ai";
// import { ProveedorModal } from "../components/proveedor/ProveedorModal";
// import { ProveedorCard } from "../components/proveedor/ProveedorCard";
// import { ItemProveedorModal } from "../components/proveedor/ItemProveedorModal"; // ← NUEVO
// import { ItemProveedorCard } from "../components/proveedor/ItemProveedorCard"; // ← NUEVO

// export const Proveedores = () => {


//     return (
//         <div className="ml-65 p-4 bg-gray-100 min-h-screen">
//             {/* Header */}
//             <div className="mb-6">
//                 <div className="flex items-center justify-between">
//                     <div className="flex items-center gap-2">
//                         <FaUserTag className="text-blue-500" size={25} />
//                         <div>
//                             <h1 className="text-2xl font-bold text-gray-800">
//                                 Gestión de Proveedores
//                             </h1>
//                         </div>
//                     </div>
//                     <button
//                         onClick={handleCreateProveedor}
//                         className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
//                     >
//                         <FaPlus size={16} />
//                         Nuevo Proveedor
//                     </button>
//                 </div>

//                 {/* Estadísticas */}
//                 <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
//                     <div className="bg-white p-4 rounded-lg shadow">
//                         <div className="flex items-center justify-between">
//                             <div>
//                                 <p className="text-gray-600 text-sm">Total Proveedores</p>
//                                 <p className="text-2xl font-bold text-blue-600">
//                                     {estadisticasGenerales?.totalProveedores || 0}
//                                 </p>
//                             </div>
//                             <FaBuilding className="text-blue-500" size={24} />
//                         </div>
//                     </div>
                    
//                     <div className="bg-white p-4 rounded-lg shadow">
//                         <div className="flex items-center justify-between">
//                             <div>
//                                 <p className="text-gray-600 text-sm">Total Productos</p>
//                                 <p className="text-2xl font-bold text-green-600">
//                                     {estadisticasGenerales?.totalProductos || 0}
//                                 </p>
//                             </div>
//                             <FaUserTag className="text-green-500" size={24} />
//                         </div>
//                     </div>
                    
//                     <button
//                         onClick={() => setShowItemsView(true)}
//                         className="group bg-gradient-to-r from-purple-500 to-purple-700 border-none cursor-pointer transition-all duration-200 hover:scale-105 hover:shadow-2xl p-4 rounded-xl shadow-lg"
//                     >
//                         <div className="flex items-center justify-between gap-4">
//                             <p className="font-bold text-white tracking-wide text-lg">Ver Productos</p>
//                             <div className="p-4 rounded-full bg-white shadow group-hover:bg-purple-100 transition-colors duration-200">
//                                 <AiFillProduct className="text-purple-600 group-hover:text-purple-800 transition-colors duration-200" size={28}/>
//                             </div>
//                         </div>
//                     </button>
//                 </div>
//             </div>

//             {/* Controles */}
//             <div className="bg-white p-4 rounded-lg shadow mb-6">
//                 <div className="flex flex-col lg:flex-row gap-4 items-center justify-between">
//                     {/* Búsqueda */}
//                     <div className="flex-1 max-w-md">
//                         <div className="relative">
//                             <FaSearch className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
//                             <input
//                                 type="text"
//                                 value={searchTerm}
//                                 onChange={handleSearch}
//                                 placeholder="Buscar por empresa, encargado, documento o email..."
//                                 className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
//                             />
//                             {searchLoading && (
//                                 <div className="absolute right-3 top-1/2 transform -translate-y-1/2">
//                                     <div className="animate-spin rounded-full h-4 w-4 border-2 border-blue-500 border-t-transparent"></div>
//                                 </div>
//                             )}
//                         </div>
//                     </div>

//                     {/* Controles de vista */}
//                     <div className="flex items-center gap-2">
//                         <span className="text-sm text-gray-600">Vista:</span>
//                         <div className="bg-gray-100 p-1 rounded-lg flex">
//                             <button
//                                 onClick={() => setViewMode('grid')}
//                                 className={`px-3 py-1 rounded text-sm transition-colors ${
//                                     viewMode === 'grid' 
//                                         ? 'bg-white text-blue-600 shadow' 
//                                         : 'text-gray-600 hover:text-gray-800'
//                                 }`}
//                             >
//                                 Tarjetas
//                             </button>
//                             <button
//                                 onClick={() => setViewMode('list')}
//                                 className={`px-3 py-1 rounded text-sm transition-colors ${
//                                     viewMode === 'list' 
//                                         ? 'bg-white text-blue-600 shadow' 
//                                         : 'text-gray-600 hover:text-gray-800'
//                                 }`}
//                             >
//                                 Lista
//                             </button>
//                         </div>
//                     </div>
//                 </div>
//             </div>

//             {/* Manejo de errores */}
//             {error && (
//                 <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
//                     <span>{error}</span>
//                     <button
//                         onClick={clearErrors}
//                         className="text-red-500 hover:text-red-700"
//                     >
//                         ✕
//                     </button>
//                 </div>
//             )}

//             {/* Contenido principal */}
//             {loading ? (
//                 <div className="flex justify-center items-center py-12">
//                     <div className="animate-spin rounded-full h-8 w-8 border-2 border-blue-500 border-t-transparent"></div>
//                     <span className="ml-2 text-gray-600">Cargando proveedores...</span>
//                 </div>
//             ) : (
//                 <>
//                     {displayProveedores.length === 0 ? (
//                         <div className="bg-white p-8 rounded-lg shadow text-center">
//                             <FaUserTag className="mx-auto text-gray-400 mb-4" size={48} />
//                             <h3 className="text-lg font-medium text-gray-900 mb-2">
//                                 No hay proveedores
//                             </h3>
//                             <p className="text-gray-600 mb-4">
//                                 {filters.searchTerm 
//                                     ? 'No se encontraron proveedores que coincidan con tu búsqueda.'
//                                     : 'Comienza agregando tu primer proveedor.'
//                                 }
//                             </p>
//                             {!filters.searchTerm && (
//                                 <button
//                                     onClick={handleCreateProveedor}
//                                     className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2"
//                                 >
//                                     <FaPlus size={16} />
//                                     Crear Primer Proveedor
//                                 </button>
//                             )}
//                         </div>
//                     ) : (
//                         <>
//                             {/* Vista de tarjetas */}
//                             {viewMode === 'grid' && (
//                                 <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
//                                     {displayProveedores.map((proveedor) => (
//                                         <ProveedorCard
//                                             key={proveedor.id}
//                                             proveedor={proveedor}
//                                             onEdit={handleEditProveedor}
//                                             onDelete={handleDeleteProveedor}
//                                             onViewItems={handleViewProveedorItems} // ← NUEVO
//                                         />
//                                     ))}
//                                 </div>
//                             )}

//                             {/* Vista de lista */}
//                             {viewMode === 'list' && (
//                                 <div className="bg-white rounded-lg shadow overflow-hidden">
//                                     <div className="overflow-x-auto">
//                                         <table className="min-w-full divide-y divide-gray-200">
//                                             <thead className="bg-gray-50">
//                                                 <tr>
//                                                     <th 
//                                                         onClick={() => handleSort('nombre_empresa')}
//                                                         className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
//                                                     >
//                                                         Empresa
//                                                     </th>
//                                                     <th 
//                                                         onClick={() => handleSort('nombre_encargado')}
//                                                         className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
//                                                     >
//                                                         Encargado
//                                                     </th>
//                                                     <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                                         Contacto
//                                                     </th>
//                                                     <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                                         Productos
//                                                     </th>
//                                                     <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
//                                                         Acciones
//                                                     </th>
//                                                 </tr>
//                                             </thead>
//                                             <tbody className="bg-white divide-y divide-gray-200">
//                                                 {displayProveedores.map((proveedor) => (
//                                                     <tr key={proveedor.id} className="hover:bg-gray-50">
//                                                         <td className="px-6 py-4 whitespace-nowrap">
//                                                             <div>
//                                                                 <div className="text-sm font-medium text-gray-900">
//                                                                     {proveedor.nombre_empresa}
//                                                                 </div>
//                                                                 <div className="text-sm text-gray-500">
//                                                                     {proveedor.numero_documento}
//                                                                 </div>
//                                                             </div>
//                                                         </td>
//                                                         <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
//                                                             {proveedor.nombre_encargado}
//                                                         </td>
//                                                         <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
//                                                             <div className="flex flex-col gap-1">
//                                                                 {proveedor.telefono && (
//                                                                     <div className="flex items-center gap-1">
//                                                                         <FaPhone className="text-gray-400" size={12} />
//                                                                         {proveedor.telefono}
//                                                                     </div>
//                                                                 )}
//                                                                 {proveedor.email && (
//                                                                     <div className="flex items-center gap-1">
//                                                                         <FaEnvelope className="text-gray-400" size={12} />
//                                                                         {proveedor.email}
//                                                                     </div>
//                                                                 )}
//                                                             </div>
//                                                         </td>
//                                                         <td className="px-6 py-4 whitespace-nowrap">
//                                                             <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
//                                                                 {proveedor.total_productos || 0} productos
//                                                             </span>
//                                                         </td>
//                                                         <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
//                                                             <div className="flex items-center gap-2">
//                                                                 <button
//                                                                     onClick={() => handleEditProveedor(proveedor)}
//                                                                     className="text-blue-600 hover:text-blue-900"
//                                                                     title="Editar"
//                                                                 >
//                                                                     <FaEdit size={16} />
//                                                                 </button>
//                                                                 <button
//                                                                     onClick={() => handleDeleteProveedor(proveedor)}
//                                                                     className="text-red-600 hover:text-red-900"
//                                                                     title="Eliminar"
//                                                                 >
//                                                                     <FaTrash size={16} />
//                                                                 </button>
//                                                             </div>
//                                                         </td>
//                                                     </tr>
//                                                 ))}
//                                             </tbody>
//                                         </table>
//                                     </div>
//                                 </div>
//                             )}

//                             {/* Paginación simple */}
//                             {!filters.searchTerm && totalPages > 1 && (
//                                 <div className="mt-6 flex justify-center items-center gap-2">
//                                     <button
//                                         onClick={() => updatePagination({ currentPage: pagination.currentPage - 1 })}
//                                         disabled={pagination.currentPage === 1}
//                                         className="px-3 py-2 bg-white border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
//                                     >
//                                         Anterior
//                                     </button>
                                    
//                                     <span className="px-4 py-2 text-sm text-gray-600">
//                                         Página {pagination.currentPage} de {totalPages}
//                                     </span>
                                    
//                                     <button
//                                         onClick={() => updatePagination({ currentPage: pagination.currentPage + 1 })}
//                                         disabled={pagination.currentPage === totalPages}
//                                         className="px-3 py-2 bg-white border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
//                                     >
//                                         Siguiente
//                                     </button>
//                                 </div>
//                             )}
//                         </>
//                     )}
//                 </>
//             )}

//             {/* ========== VISTA DE ITEM-PROVEEDORES ========== */}
//             {showItemsView && (
//                 <div className="fixed inset-0 bg-gray-100 z-40 overflow-y-auto">
//                     <div className="p-4">
//                         {/* Header de Items */}
//                         <div className="mb-6">
//                             <div className="flex items-center justify-between">
//                                 <div className="flex items-center gap-3">
//                                     <button
//                                         onClick={handleBackToProveedores}
//                                         className="p-2 hover:bg-gray-200 rounded-lg transition-colors"
//                                         title="Volver a proveedores"
//                                     >
//                                         <FaArrowLeft className="text-gray-600" size={20} />
//                                     </button>
//                                     <FaBox className="text-green-500" size={25} />
//                                     <div>
//                                         <h1 className="text-2xl font-bold text-gray-800">
//                                             {selectedProveedor 
//                                                 ? `Items de ${selectedProveedor.nombre_empresa}`
//                                                 : 'Gestión de Items'
//                                             }
//                                         </h1>
//                                         <p className="text-gray-600">
//                                             {selectedProveedor 
//                                                 ? `Productos suministrados por ${selectedProveedor.nombre_empresa}`
//                                                 : 'Administra todos los items de tus proveedores'
//                                             }
//                                         </p>
//                                     </div>
//                                 </div>
//                                 <button
//                                     onClick={handleCreateItem}
//                                     className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
//                                 >
//                                     <FaPlus size={16} />
//                                     Nuevo Item
//                                 </button>
//                             </div>
//                         </div>

//                         {/* Manejo de errores de items */}
//                         {itemsError && (
//                             <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
//                                 <span>{itemsError}</span>
//                                 <button
//                                     onClick={clearItemErrors}
//                                     className="text-red-500 hover:text-red-700"
//                                 >
//                                     ✕
//                                 </button>
//                             </div>
//                         )}

//                         {/* Contenido de Items */}
//                         {itemsLoading ? (
//                             <div className="flex justify-center items-center py-12">
//                                 <div className="animate-spin rounded-full h-8 w-8 border-2 border-green-500 border-t-transparent"></div>
//                                 <span className="ml-2 text-gray-600">Cargando items...</span>
//                             </div>
//                         ) : (
//                             <>
//                                 {itemProveedores.length === 0 ? (
//                                     <div className="bg-white p-8 rounded-lg shadow text-center">
//                                         <FaBox className="mx-auto text-gray-400 mb-4" size={48} />
//                                         <h3 className="text-lg font-medium text-gray-900 mb-2">
//                                             No hay items
//                                         </h3>
//                                         <p className="text-gray-600 mb-4">
//                                             {selectedProveedor 
//                                                 ? `${selectedProveedor.nombre_empresa} aún no tiene items registrados.`
//                                                 : 'Comienza agregando el primer item.'
//                                             }
//                                         </p>
//                                         <button
//                                             onClick={handleCreateItem}
//                                             className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2"
//                                         >
//                                             <FaPlus size={16} />
//                                             Crear Primer Item
//                                         </button>
//                                     </div>
//                                 ) : (
//                                     <div className="flex justify-center items-center">
//                                         <table className="table-auto max-w-350 bg-white rounded-lg shadow overflow-hidden">
//                                             <thead>
//                                                 <tr>
//                                                     <th className="w-200 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Nombre</th>
//                                                     <th className="px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Código</th>
//                                                     <th className="w-50 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Proveedor</th>
//                                                     <th className="w-30 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Precio</th>
//                                                     <th className="w-30 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Precio con IVA</th>
//                                                     <th className="w-30 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Unidad</th>
//                                                     <th className="w-20 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Stock</th>
//                                                     <th className="w-20 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Acciones</th>
//                                                 </tr>
//                                             </thead>
//                                             <tbody>
//                                                 {itemProveedores.map(item => (
//                                                     <ItemProveedorCard
//                                                         key={item.id}
//                                                         itemProveedor={item}
//                                                         onEdit={handleEditItem}
//                                                         onDelete={handleDeleteItem}
//                                                         onView={handleViewItem}
//                                                     />
//                                                 ))}
//                                             </tbody>
//                                         </table>
//                                     </div>
//                                 )}
//                             </>
//                         )}
//                     </div>
//                 </div>
//             )}

//             {/* ========== MODALES Y DIÁLOGOS ========== */}

//             {/* Modal de proveedor (existente) */}
//             {showModal && (
//                 <ProveedorModal
//                     proveedor={editingProveedor}
//                     onSubmit={handleSubmitProveedor}
//                     onClose={() => {
//                         setShowModal(false);
//                         setEditingProveedor(null);
//                     }}
//                 />
//             )}

//             {/* Modal de item-proveedor (NUEVO) */}
//             {showItemModal && (
//                 <ItemProveedorModal
//                     itemProveedor={editingItem}
//                     proveedores={paginatedProveedores} // Pasar lista de proveedores
//                     onSubmit={handleSubmitItem}
//                     onClose={() => {
//                         setShowItemModal(false);
//                         setEditingItem(null);
//                     }}
//                 />
//             )}

//             {/* Diálogo de confirmación de proveedor (existente) */}
//             {showConfirmDialog && (
//                 <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
//                     <div className="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
//                         <h3 className="text-lg font-medium text-gray-900 mb-2">
//                             Eliminar Proveedor
//                         </h3>
//                         <p className="text-gray-600 mb-4">
//                             ¿Estás seguro de que deseas eliminar al proveedor "{proveedorToDelete?.nombre_empresa}"?
//                         </p>
//                         <div className="flex justify-end gap-3">
//                             <button
//                                 onClick={() => {
//                                     setShowConfirmDialog(false);
//                                     setProveedorToDelete(null);
//                                 }}
//                                 className="px-4 py-2 text-black bg-gray-100 hover:bg-gray-200 rounded-lg"
//                             >
//                                 Cancelar
//                             </button>
//                             <button
//                                 onClick={confirmDelete}
//                                 className="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg"
//                             >
//                                 Eliminar
//                             </button>
//                         </div>
//                     </div>
//                 </div>
//             )}

//             {/* Diálogo de confirmación de item (NUEVO) */}
//             {showItemConfirmDialog && (
//                 <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
//                     <div className="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
//                         <h3 className="text-lg font-medium text-gray-900 mb-2">
//                             Eliminar Item
//                         </h3>
//                         <p className="text-gray-600 mb-4">
//                             ¿Estás seguro de que deseas eliminar el item "{itemToDelete?.nombre_item}"?
//                         </p>
//                         <div className="flex justify-end gap-3">
//                             <button
//                                 onClick={() => {
//                                     setShowItemConfirmDialog(false);
//                                     setItemToDelete(null);
//                                 }}
//                                 className="px-4 py-2 text-black bg-gray-100 hover:bg-gray-200 rounded-lg"
//                             >
//                                 Cancelar
//                             </button>
//                             <button
//                                 onClick={confirmDeleteItem}
//                                 className="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg"
//                             >
//                                 Eliminar
//                             </button>
//                         </div>
//                     </div>
//                 </div>
//             )}
//         </div>
//     );
// };