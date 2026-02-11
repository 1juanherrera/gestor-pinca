import { useState } from 'react';
import { useCostosItem } from '../../hooks/useCostosItem';
import { MdClose, MdAddCircleOutline } from 'react-icons/md';

export const FormCostProducts = ({ onClose, idBodega, idEdit, showForm, productDetail }) => {

    const { updateItem } = useCostosItem();
    const [formData, setFormData] = useState({
        
    })

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-hidden">

                <div className="bg-blue-500 text-white p-6 flex justify-between items-center">
                    <h2 className="text-2xl font-bold flex items-center gap-2">
                        <MdAddCircleOutline size={28} /> {showForm ? 'Editar Item' : 'Crear Nuevo Item'}
                    </h2>
                    <button type="button" className="hover:bg-blue-600 cursor-pointer rounded-full p-1 transition-colors" onClick={onClose}><MdClose size={28} /></button>
                </div>
            
            </div>
        </div>
    )
}