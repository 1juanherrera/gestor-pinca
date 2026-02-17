import { useState, useEffect } from 'react';
import { useCostosItem } from '../../hooks/useCostosItem';
import { MdClose } from 'react-icons/md';
import { FaEdit, FaSave } from 'react-icons/fa';
import { InputMoneda } from '../InputMoneda';

export const FormCostProducts = ({ onClose, idEdit, name, setShowForm, productDetail, eventToast }) => {

    const { updateItem, updateError, isUpdating } = useCostosItem();

    const inputClasses = "w-full bg-white text-sm px-3 py-[8px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 ease-in-out placeholder-gray-300 text-gray-800 shadow-sm";
    const labelClasses = "block text-sm font-semibold text-gray-700 mb-1 uppercase";
    
    const [formData, setFormData] = useState({
        envase: "",
        etiqueta: "",
        bandeja: "",
        plastico: "",
        costo_mp_galon: "",
        costo_mg_kg: "",
        costo_mod: ""
    });

    useEffect(() => {
        if (productDetail?.costos) {
            setFormData(prev => ({
                ...prev,
                [name]: productDetail.costos[name] || ""
            }));
        }
    }, [productDetail, name]);

    if (!productDetail) return <div className="p-10 text-center">Cargando datos...</div>;

    const handleSubmit = (e) => {
        e.preventDefault();
        
        const dataToSend = { [name]: formData[name] };

        updateItem({ id: idEdit, data: dataToSend }, {
            onSuccess: () => {
                setShowForm(false);
                eventToast("Costo actualizado exitosamente", "success");
                onClose();
            }
        });
    }

    function removeUnderscores(texto) {
        return texto ? texto.replace(/_/g, ' ') : "";
    }

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in duration-200">
                
                {/* Header */}
                <div className="bg-blue-500 text-white p-4 flex justify-between items-center">
                    <h2 className="text-xl font-semibold flex items-center gap-2 capitalize">
                        <FaEdit size={24} /> Editar {removeUnderscores(name)}
                    </h2>
                    <button onClick={onClose} className="hover:bg-blue-600 cursor-pointer rounded-full p-1"><MdClose size={24} /></button>
                </div>

                {/* Agregamos el onSubmit aquí */}
                <form className="p-6 space-y-4" onSubmit={handleSubmit}>
                    
                    {updateError && (
                        <div className="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p className="text-red-600 text-sm">{updateError?.message || "Error al actualizar"}</p>
                        </div>
                    )}

                    <div>
                        <InputMoneda
                            label={`Nuevo valor para ${removeUnderscores(name)}`}
                            name={name}
                            value={formData[name]}
                            onChange={(e) => setFormData(prev => ({ ...prev, [name]: e.target.value }))}
                            labelClasses={labelClasses}
                            className={inputClasses}
                        />
                    </div>

                    {/* Botones de acción que faltaban */}
                    <div className="flex justify-end gap-3 pt-4 border-t border-gray-300">
                        <button 
                            type="button" 
                            onClick={onClose} 
                            className="px-4 cursor-pointer py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            disabled={isUpdating}
                            className={`flex cursor-pointer items-center gap-2 px-6 py-2 text-sm font-semibold text-white rounded-lg transition-all 
                                ${isUpdating ? 'bg-gray-400' : 'bg-blue-500 hover:bg-blue-600'}`}
                        >
                            <FaSave /> {isUpdating ? 'Guardando...' : 'Guardar'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}