import React from 'react';
import { FaUserTag, FaPlus, FaEdit, FaTrash, FaBuilding, FaEnvelope, FaPhone, FaArrowLeft, FaBox, FaBarcode, FaSearch } from "react-icons/fa";
import { AiFillProduct } from "react-icons/ai";
import { ProveedorForm } from "../components/proveedor/ProveedorForm";
import { ItemProveedorTable } from "../components/proveedor/ItemProveedorTable"; 
import { useState } from "react";
import { formatoPesoColombiano, parsePesoColombiano, stableItemId } from "../utils/formatters";
import { useProveedores } from "../hooks/useProveedores";
import { ProveedorFormEdit } from "../components/proveedor/ProveedorFormEdit";

export const Proveedores = () => {

    const { 
        data: proveedores, 
        itemData: itemProveedores,
        isLoading, 
        error, 
        refreshData, 
        create, 
        isCreating, 
        createError,
        remove,
        // isDeleting: isRemoving,
        update,
        isUpdating,
        updateError,
    } = useProveedores();

    const [form, setForm] = useState({
        nombre_encargado: "",
        nombre_empresa: "",
        numero_documento: "",
        direccion: "",
        telefono: "",
        email: ""
    })

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm((prev) => ({ 
        ...prev, 
        [name]: value 
        }))
    }

    // const [showEdit, setShowEdit] = useState(false);
    const [show, setShow] = useState(false);
    const [showCreateProveedor, setShowCreateProveedor] = useState(false);
    const [openItems, setOpenItems] = useState(null);
    const [selectedItemIds, setSelectedItemIds] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [proveedorEdit, setProveedorEdit] = useState(null);
    const [showEdit, setShowEdit] = useState(false);

    if (isLoading) return <p>Cargando proveedores...</p>;
    if (error) return <p>Error al cargar proveedores</p>;
    if (!proveedores || proveedores.length === 0) return <p>No hay proveedores registradas</p>;

    const handle = (id) => {
        if (window.confirm("¿Seguro que deseas eliminar este proveedor?")) {
            remove(id);
        }
    }

    // Filtramos los items según el texto buscado (por nombre o código, por ejemplo)
    const filteredItems = itemProveedores.filter(item =>
        item.nombre?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.codigo?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.nombre_empresa?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    const toggleProveedor = (id) => {
        setOpenItems(openItems === id ? null : id);
    };

    const toggleSelectItem = (id) => {
        setSelectedItemIds(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
    }


    return (
        <div className="ml-65 p-4 bg-gray-100 min-h-screen">
            {/* Header */}
            <div className="mb-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <FaUserTag className="text-blue-500" size={25} />
                        <div>
                            <h1 className="text-2xl font-bold text-gray-800">
                                Gestión de Proveedores
                            </h1>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <button
                            className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                        >
                            <FaPlus size={16} />
                            Nuevo Producto
                        </button>
                        <button
                            onClick={() => setShowCreateProveedor(true)}
                            className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                        >
                            <FaPlus size={16} />
                            Nuevo Proveedor
                        </button>
                    </div>
                </div>

                {/* Estadísticas */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div className="bg-white p-4 rounded-lg shadow">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-sm">Total Proveedores</p>
                                <p className="text-2xl font-bold text-blue-600">
                                    {proveedores?.length || 0}
                                </p>
                            </div>
                            <FaBuilding className="text-blue-500" size={24} />
                        </div>
                    </div>
                    
                    <div className="bg-white p-4 rounded-lg shadow">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-gray-600 text-sm">Total Productos</p>
                                <p className="text-2xl font-bold text-green-600">
                                    {itemProveedores?.length || 0}
                                </p>
                            </div>
                            <FaUserTag className="text-green-500" size={24} />
                        </div>
                    </div>
                    
                    <button onClick={() => setShow(true)}
                        className="group bg-linear-to-r from-gray-500 to-gray-600 border-none cursor-pointer hover:shadow-2xl p-4 rounded-xl shadow-lg"
                    >
                        <div className="flex items-center justify-between gap-4">
                            <p className="font-bold text-white tracking-wide text-lg">Total Productos</p>
                            <div className="p-4 rounded-full bg-white shadow group-hover:bg-gray-100 transition-colors duration-200">
                                <AiFillProduct className="text-gray-600 group-hover:text-gray-800" size={28}/>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div className="bg-white rounded-lg shadow overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th
                                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                >
                                    Empresa
                                </th>
                                <th
                                    className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                >
                                    Encargado
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contacto
                                </th>
                                <th className="px-6 py-3 text-left pl-8 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Productos
                                </th>
                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                    {proveedores.map((proveedor) => (
                        <React.Fragment key={proveedor.id_proveedor}>
                            <tr
                                
                                onClick={() => toggleProveedor(proveedor.id_proveedor)}
                                className="hover:bg-gray-200 cursor-pointer transition-colors"
                                title="Ver productos"
                            >
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div className="text-sm font-semibold text-gray-900 uppercase flex items-center gap-2">
                                            {proveedor.nombre_empresa}
                                        </div>
                                        <div className="text-sm text-gray-500">{proveedor.numero_documento}</div>
                                    </div>
                                </td>

                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div className="flex items-center gap-2">
                                        <FaUserTag />
                                        {proveedor.nombre_encargado}
                                    </div>
                                </td>

                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div className="flex flex-col gap-1">
                                        {proveedor.telefono && (
                                            <div className="flex items-center gap-1">
                                                <FaPhone className="text-gray-400" size={12} />
                                                {proveedor.telefono}
                                            </div>
                                        )}
                                        {proveedor.email && (
                                            <div className="flex items-center gap-1">
                                                <FaEnvelope className="text-gray-400" size={12} />
                                                {proveedor.email}
                                            </div>
                                        )}
                                    </div>
                                </td>

                                <td className="px-6 py-2 text-start whitespace-nowrap">
                                    <span className="inline-flex shadow-sm border border-blue-200 items-center px-4 py-2 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {proveedor?.items?.length || 0} productos
                                    </span>
                                </td>

                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div className="flex items-center justify-center gap-2">
                                        <button     
                                            className="p-2 text-white rounded-md transition-colors bg-gray-500 hover:bg-gray-800 cursor-pointer"
                                            title="Editar"
                                            onClick={e => {
                                                e.preventDefault();
                                                e.stopPropagation(); 
                                                setShowEdit(true);
                                                setProveedorEdit(proveedor);
                                            }}
                                        >
                                            <FaEdit size={14} />
                                        </button>
                                        <button
                                            onClick={() => handle(proveedor.id_proveedor)}
                                            className="p-2 bg-red-500 text-white hover:bg-red-800 rounded-md transition-colors cursor-pointer"
                                            title="Eliminar"
                                        >
                                            <FaTrash size={14} />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        {/* DESPLEGABLE DE PRODUCTOS */}
                        {openItems === proveedor.id_proveedor && (
                            <tr className="bg-gray-100">
                                <td colSpan={5} className="px-6 py-3">
                                    {proveedor.items && proveedor.items.length > 0 ? (
                                        <div className="overflow-x-aut">
                                            <table className="w-full text-sm">
                                                <thead>
                                                    <tr className="text-gray-600 border-b border-gray-300">
                                                        <th className="text-left py-2">Código</th>
                                                        <th className="text-left py-2">Nombre</th>
                                                        <th className="text-left py-2">Tipo</th>
                                                        <th className="text-left py-2 pl-8">Precio</th>
                                                        <th className="text-left py-2">Precio + IVA</th>
                                                        <th className="text-center py-2">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {proveedor.items.map((item) => (
                                                        (() => {
                                                            const itemId = stableItemId(item, proveedor.id_proveedor);
                                                            return (
                                                                <tr
                                                                    key={itemId}
                                                                    className="border-b border-gray-300 last:border-0 hover:bg-gray-200"
                                                                >
                                                                    <td className="py-2">
                                                                        <div className="flex items-center justify-start gap-2">
                                                                            <FaBarcode />{item.codigo}
                                                                        </div>
                                                                    </td>
                                                                    <td className="py-2 flex items-center gap-2 uppercase">
                                                                        <FaBox className="text-green-500" /> {item.nombre}
                                                                    </td>
                                                                    <td className="py-2 uppercase">{item.tipo}</td>
                                                                    <td className="py-2 text-left">
                                                                        {item.precio_unitario || 0}/{item.unidad_empaque}
                                                                    </td>
                                                                    <td className="py-2 text-left">
                                                                        {item.precio_con_iva}
                                                                    </td>
                                                                    <td className="py-2 gap-2 uppercase">
                                                                        <div className="flex justify-center gap-2">
                                                                            <button     
                                                                                className="p-2 text-white rounded-md transition-colors bg-gray-500 hover:bg-gray-800 cursor-pointer"
                                                                                title="Editar"
                                                                            >
                                                                                <FaEdit size={14} />
                                                                            </button>
                                                                            <button
                                                                                className="p-2 bg-red-500 text-white hover:bg-red-800 rounded-md transition-colors cursor-pointer"
                                                                                title="Eliminar"
                                                                            >
                                                                                <FaTrash size={14} />
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            );
                                                        })()
                                                    ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <div className="text-center text-gray-400 py-4">
                                        No hay productos registrados para este proveedor.
                                    </div>
                                )}
                                    {(() => {
                                        const selectedItems = proveedor.items.map((it) => {
                                            const id = stableItemId(it, proveedor.id_proveedor);
                                            return selectedItemIds.includes(id) ? { ...it, _id: id } : null;
                                        }).filter(Boolean);

                                        if (selectedItems.length === 0) return null;

                                        let itemsWithCost = selectedItems.map(it => ({
                                            ...it,
                                            _cost: parsePesoColombiano(it.precio_con_iva ?? it.precio_unitario ?? 0)
                                        }));
                                        // ordenar ascendente por costo para mostrar el más barato primero
                                        itemsWithCost.sort((a, b) => a._cost - b._cost);
                                        const cheapest = itemsWithCost[0] || null;

                                        return (
                                            <div className="mt-3 bg-white border border-gray-200 rounded-md p-3">
                                                <div className="flex items-center justify-between mb-2">
                                                    <div className="text-sm font-medium">Comparar precios ({selectedItems.length})</div>
                                                    <div className="flex items-center gap-2">
                                                        <button
                                                            className="px-3 py-1 text-sm bg-gray-200 rounded-md"
                                                            onClick={() => setSelectedItemIds([])}
                                                        >Limpiar</button>
                                                    </div>
                                                </div>
                                                <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                    {itemsWithCost.map(it => {
                                                        const savings = it._cost - (cheapest?._cost || 0);
                                                        const pct = it._cost > 0 ? (savings / it._cost) * 100 : 0;
                                                        const isCheapest = cheapest && it._id === cheapest._id;
                                                        return (
                                                            <div key={it._id} className={`p-3 border rounded ${isCheapest ? 'border-green-400 bg-green-50' : ''}`}>
                                                                <div className="text-sm font-semibold">{it.nombre}</div>
                                                                <div className="text-xs text-gray-500">{it.codigo}</div>
                                                                <div className="mt-2 text-sm font-medium">Precio c/IVA: <span className="text-emerald-700">{formatoPesoColombiano(it._cost)}</span></div>
                                                                {!isCheapest && (
                                                                    <div className="text-xs text-gray-600 mt-1">Ahorra {formatoPesoColombiano(savings)} ({pct.toFixed(1)}%) si eliges el más barato</div>
                                                                )}
                                                                {isCheapest && (
                                                                    <div className="inline-block mt-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Más barato</div>
                                                                )}
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                        );
                                    })()}
                                </td>
                            </tr>
                        )}
                                </React.Fragment>
                            ))}
            </tbody>
        </table>
    </div>
</div>

            {/* ========== VISTA DE ITEM-PROVEEDORES ========== */}
            {show && (
                <div className="fixed inset-0 bg-gray-100 z-40 overflow-y-auto">
                    <div className="p-4">
                        {/* Header de Items */}
                        <div className="mb-6">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <button
                                        onClick={() => setShow(false)}
                                        className="p-2 hover:bg-gray-200 rounded-lg transition-colors"
                                        title="Volver a proveedores"
                                    >
                                        <FaArrowLeft className="text-gray-600" size={20} />
                                    </button>
                                    <FaBox className="text-green-500" size={25} />
                                    <div>
                                        <h1 className="text-2xl font-bold text-gray-800">
                                            Gestión de Items
                                        </h1>
                                        <p className="text-gray-600">
                                            Administra todos los items de tus proveedores
                                        </p>
                                    </div>
                                </div>
                                <div className="relative w-full max-w-md">
                                <FaSearch className="absolute left-3 top-2.5 text-gray-400" />
                                <input
                                    type="text"
                                    placeholder="Buscar por nombre, código o proveedor..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                        className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>
                                <button
                                    className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                                >
                                    <FaPlus size={16} />
                                    Nuevo Producto
                                </button>
                            </div>
                        </div>

                        {/* Manejo de errores de items */}
                        {error && (
                            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
                                <span>{error}</span>
                                <button
                                    className="text-red-500 hover:text-red-700"
                                >
                                    ✕
                                </button>
                            </div>
                        )}

                        {/* Contenido de Items */}
                        {isLoading ? (
                        <div className="flex justify-center items-center py-12">
                            <div className="animate-spin rounded-full h-8 w-8 border-2 border-green-500 border-t-transparent"></div>
                            <span className="ml-2 text-gray-600">Cargando items...</span>
                        </div>
                        ) : (
                            <>
                                {filteredItems.length === 0 ? (
                                    <div className="bg-white p-8 rounded-lg shadow text-center">
                                        <FaBox className="mx-auto text-gray-400 mb-4" size={48} />
                                        <h3 className="text-lg font-medium text-gray-900 mb-2">No hay items</h3>
                                        <p className="text-gray-600 mb-4">
                                            {itemProveedores.length === 0
                                                ? 'Aún no tiene items registrados.'
                                                : 'No se encontraron coincidencias.'}
                                        </p>
                                        <button className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2">
                                            <FaPlus size={16} />
                                            Crear Producto
                                        </button>
                                    </div>
                                ) : (
                                    <div className="flex justify-center items-center">
                                        <table className="table-auto max-w-350 bg-white rounded-lg shadow overflow-hidden">
                                            <thead>
                                                <tr>
                                                    <th className="w-12 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Sel</th>
                                                    <th className="w-200 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Nombre</th>
                                                    <th className="px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Código</th>
                                                    <th className="w-50 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Proveedor</th>
                                                    <th className="w-30 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Precio</th>
                                                    <th className="w-30 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Precio con IVA</th>
                                                    <th className="w-30 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Unidad</th>
                                                    <th className="w-20 px-4 py-2 text-center text-sm font-semibold text-black bg-gray-100">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {filteredItems.map((item) => {
                                                    const itemId = stableItemId(item);
                                                    return (
                                                        <ItemProveedorTable
                                                            key={itemId}
                                                            itemProveedor={item}
                                                            itemId={itemId}
                                                            selected={selectedItemIds.includes(itemId)}
                                                            onToggleSelect={toggleSelectItem}
                                                        />
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </>
                        )}
                        {/* Comparacion de precios */}
                        {selectedItemIds.length > 0 && (() => {
                            const selectedItems = itemProveedores.map((it) => {
                                const id = stableItemId(it);
                                return selectedItemIds.includes(id) ? { ...it, _id: id } : null;
                            }).filter(Boolean);

                            if (selectedItems.length === 0) return null;

                            let itemsWithCost = selectedItems.map(it => ({ ...it, _cost: parsePesoColombiano(it.precio_con_iva ?? it.precio_unitario ?? 0) }));
                            // ordenar ascendente por costo para que el más barato quede al inicio
                            itemsWithCost.sort((a, b) => a._cost - b._cost);
                            const cheapest = itemsWithCost[0] || null;

                            return (
                                <div className="mt-4 bg-white p-4 rounded-lg shadow w-full">
                                    <div className="flex items-center justify-between mb-3">
                                        <div className="font-semibold">Comparador de precios ({selectedItems.length})</div>
                                        <div className="flex gap-2">
                                            <button
                                                className="px-3 py-1 bg-gray-200 rounded-md"
                                                onClick={() => setSelectedItemIds([])}
                                            >Limpiar</button>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-5 gap-3 ">
                                        {itemsWithCost.map(it => {
                                            const savings = it._cost - (cheapest?._cost || 0);
                                            const pct = it._cost > 0 ? (savings / it._cost) * 100 : 0;
                                            const isCheapest = cheapest && it._id === cheapest._id;
                                            return (
                                                <div key={it._id} className={`p-3 border rounded ${isCheapest ? 'border-green-400 bg-green-50' : 'border-gray-300'}`}>
                                                    <div className="text-sm font-semibold">{it.nombre}</div>
                                                    <div className="text-xs text-gray-500">{it.nombre_empresa || 'Sin empresa'}</div>
                                                    <div className="mt-2 text-sm font-medium">Precio c/IVA: <span className="text-emerald-700">{formatoPesoColombiano(it._cost)}</span></div>
                                                    {!isCheapest && (
                                                        <div className="text-xs text-gray-600 mt-1">Ahorra {formatoPesoColombiano(savings)} ({pct.toFixed(1)}%) si eliges el más barato</div>
                                                    )}
                                                    {isCheapest && (
                                                        <div className="inline-block mt-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Más barato</div>
                                                    )}
                                                </div>
                                            )
                                        })}
                                    </div>
                                </div>
                            )
                        })()}
                    </div>
                </div>
            )}

            {/* ========== MODALES Y DIÁLOGOS ========== */}

            {/* Modal de proveedor (existente) */}
            {showCreateProveedor && (
                <ProveedorForm
                    onSubmit={() => {
                        setShowCreateProveedor(false);
                        refreshData();
                    }}
                    setShowCreate={setShowCreateProveedor}
                    create={create}
                    isCreating={isCreating}
                    proveedor={proveedores}
                    createError={createError}
                    form={form}
                    setForm={setForm}
                    handleChange={handleChange}
                />
            )}

            {showEdit && (
                <ProveedorFormEdit
                    onSubmit={() => {
                        setShowEdit(false);
                        refreshData();
                    }}
                    setShowEdit={setShowEdit}
                    update={update}
                    isUpdating={isUpdating}
                    proveedor={proveedores}
                    proveedorEdit={proveedorEdit}
                    updateError={updateError}
                    refreshData={refreshData}
                    setProveedorEdit={setProveedorEdit}
                />
            )}

            {/* Diálogo de confirmación de proveedor (existente) */}
            {/* {showConfirmDialog && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
                        <h3 className="text-lg font-medium text-gray-900 mb-2">
                            Eliminar Proveedor
                        </h3>
                        <p className="text-gray-600 mb-4">
                            ¿Estás seguro de que deseas eliminar al proveedor "{proveedorToDelete?.nombre_empresa}"?
                        </p>
                        <div className="flex justify-end gap-3">
                            <button
                                onClick={() => {
                                    setShowConfirmDialog(false);
                                    setProveedorToDelete(null);
                                }}
                                className="px-4 py-2 text-black bg-gray-100 hover:bg-gray-200 rounded-lg"
                            >
                                Cancelar
                            </button>
                            <button
                                onClick={confirmDelete}
                                className="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            )} */}

            {/* Diálogo de confirmación de item (NUEVO) */}
            {/* {showItemConfirmDialog && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
                        <h3 className="text-lg font-medium text-gray-900 mb-2">
                            Eliminar Item
                        </h3>
                        <p className="text-gray-600 mb-4">
                            ¿Estás seguro de que deseas eliminar el item "{itemToDelete?.nombre_item}"?
                        </p>
                        <div className="flex justify-end gap-3">
                            <button
                                onClick={() => {
                                    setShowItemConfirmDialog(false);
                                    setItemToDelete(null);
                                }}
                                className="px-4 py-2 text-black bg-gray-100 hover:bg-gray-200 rounded-lg"
                            >
                                Cancelar
                            </button>
                            <button
                                onClick={confirmDeleteItem}
                                className="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            )} */}
        </div>
    );
};