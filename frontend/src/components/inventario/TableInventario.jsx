import { FaTrash, FaEdit } from "react-icons/fa";
import { MdSwapHorizontalCircle  } from "react-icons/md";
import { formatoPesoColombiano, validarEntero } from "../../utils/formatters";
import { useItems } from "../../hooks/useItems";
import { useToast } from "../../hooks/useToast";
import { Toast } from "../Toast";
import { useState } from "react";
import { ItemForm } from "./ItemForm";
import { TraspasoModal } from "./TraspasoModal";
import { useBodegas } from "../../hooks/useBodegas";

export const TableInventario = ({ items = [], refreshItems, idBodega }) => {

    const { removeItem } = useItems();
    const { bodegas } = useBodegas();
    const [idEdit, setIdEdit] = useState(null);

    const [showForm, setShowForm] = useState(false);
    const [showTraspaso, setShowTraspaso] = useState(false);
    const [itemSeleccionado, setItemSeleccionado] = useState(null);

    const getNombre = (item) => item?.nombre_item_general || item.nombre || '-';
    const getCodigo = (item) => item?.codigo_item_general || item.codigo || '-';
    const getTipo = (item) => (item?.nombre_tipo || item.tipo || '-').toUpperCase();
    const getPrecio = (item) => item?.precio_venta || '-';
    const getId = (item) => item?.id_item_general || item.id || '-';
    const getCostoGalon = (item) => item?.costo_mp_galon || '-';

    const handleType = (item) => {
        const tipo = getTipo(item);
        switch (tipo) {
            case '0':
                return 'bg-blue-100 text-blue-700';
            case '1':
                return 'bg-purple-100 text-purple-700';
            case '2':
                return 'bg-yellow-100 text-yellow-700';
            default:
                return 'bg-gray-100 text-gray-700';
        }
    }

    const {
        toastVisible,
        toastMessage,
        toastType,

        eventToast,
        setToastVisible
    } = useToast();

    const handleDelete = (id, name, deleteFunc) => {
        if (window.confirm(`¿Seguro que deseas eliminar ${name}?`)) {
            eventToast(`${name} eliminado correctamente`, "success");
            deleteFunc(id);
        }
    }

    return (
        <>
            <div className="overflow-hidden rounded-lg border mt-3 border-gray-200">
                <div className="max-h-[63vh] overflow-y-auto">
                    <table className="w-full border-collapse">
                        <thead className="text-white uppercase">
                            <tr>
                                <th className="px-4 py-2 sticky top-0 z-20 bg-gray-700 text-center text-xs font-medium w-15">#</th>
                                <th className="px-4 py-2 sticky top-0 z-20 bg-gray-700 text-center text-xs font-medium w-25">Codigo</th>
                                <th className="px-4 py-2 sticky top-0 z-20 bg-gray-700 text-left text-xs font-medium">Nombre</th>
                                <th className="px-4 py-2 sticky top-0 z-20 bg-gray-700 text-center text-xs font-medium w-32">Cantidad</th>
                                <th className="px-4 py-2 sticky top-0 z-20 bg-gray-700 text-center text-xs font-medium w-32">Tipo</th>
                                <th className="px-4 py-2 sticky top-0 z-20 bg-gray-700 text-center text-xs font-medium w-32">Unidad</th>
                                <th className="px-4 py-2 sticky top-0 z-20 bg-gray-700 text-center text-xs font-medium w-32">Costo</th>
                                <th className="px-4 py-2 sticky top-0 z-20 bg-gray-700 text-center text-xs font-medium w-28">Precio</th>
                                <th className="px-4 py-2 sticky top-0 z-20 bg-gray-700 text-center text-xs font-medium w-32">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="bg-gray-100">
                            {items.map((producto, index) => (
                                <tr 
                                    key={index} 
                                    className={`border-b border-gray-300 hover:bg-gray-300 
                                        relative hover:z-10 hover:ring-2 hover:ring-inset hover:ring-gray-400 transition-colors ${
                                        index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                    }`}
                                >
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {index + 1 || '-'}
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {getCodigo(producto)}
                                        </span>
                                    </td>
                                    <td className="p-1 border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900 uppercase">
                                            {getNombre(producto)}
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {validarEntero(producto.cantidad)}
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className={`px-3 py-1 block text-xs font-medium rounded-md ${handleType(producto)}`}>
                                            {
                                                getTipo(producto) == '0' ? 
                                                'PRODUCTO' : getTipo(producto) == '1' ? 
                                                'MATERIA PRIMA' : getTipo(producto) == '2' ? 
                                                'INSUMO' : getTipo(producto)
                                            }
                                        </span>
                                    </td>
                                    <td className="p-1 text-center border border-gray-200">
                                        <span className="text-xs font-medium text-gray-900">
                                            {producto.unidad || '-'}
                                        </span>
                                    </td>
                                    <td className="p-1 text-left border border-gray-200">
                                        <span className="text-xs font-semibold text-emerald-800 pl-6">
                                            {formatoPesoColombiano(getCostoGalon(producto))}
                                        </span>
                                    </td>
                                    <td className="p-1 text-left border border-gray-200">
                                        <span className="text-xs font-semibold text-emerald-800 pl-6">
                                            {formatoPesoColombiano(getPrecio(producto))}
                                        </span>
                                    </td>
                                    <td className="p-1 py-1 border border-gray-200">
                                        <div className="flex justify-center gap-2">
                                                <button 
                                                onClick={() => (
                                                    setShowForm(true),   
                                                    setIdEdit(getId(producto))
                                                )}
                                                    className="p-2 text-white duration-200 transform hover:scale-110 rounded-md transition-colors bg-gray-500 hover:bg-gray-800 cursor-pointer"
                                                    title="Editar"
                                                >
                                                    <FaEdit size={14} />
                                                </button>
                                                <button 
                                                    onClick={() => handleDelete(getId(producto), getNombre(producto), removeItem)}
                                                    className="p-2 duration-200 transform hover:scale-110 bg-red-500 text-white hover:bg-red-800 rounded-md transition-colors cursor-pointer"
                                                    title="Eliminar"
                                                >
                                                    <FaTrash size={14} />
                                                </button>
                                                <button 
                                                    onClick={() => (
                                                        setShowTraspaso(true),
                                                        setItemSeleccionado(producto)
                                                    )} 
                                                    className="p-2 duration-200 transform hover:scale-110 bg-blue-500 text-white hover:bg-blue-800 rounded-md transition-colors cursor-pointer"
                                                    title="Cambiar de bodega"
                                                >
                                                    <MdSwapHorizontalCircle  size={20} />
                                                </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

        {/* Toast */}           
        {toastVisible && (
            <Toast
                message={toastMessage} 
                type={toastType}
                onClose={() => setToastVisible(false)}
            />
        )}
        {showForm && (
            <ItemForm 
                idBodega={idBodega}
                idEdit={idEdit}
                refreshItems={refreshItems}
                showForm={showForm}
                onClose={() => {
                    setIdEdit(null);
                    setShowForm(false);
                }}
            />
        )}
        {showTraspaso && (
            <TraspasoModal 
                item={itemSeleccionado}
                bodegas={bodegas} 
                onClose={() => setShowTraspaso(false)}
                onConfirm={(data) => {
                    console.log('Traspaso confirmado:', data);
                    setShowTraspaso(false);
                }}
                isSubmitting={false}
            />
        )}
        </>
    )
}