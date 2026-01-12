import { MdSave, MdClose, MdAdd, MdDelete, MdAddCircleOutline, MdArticle } from 'react-icons/md';
import { FaMicroscope, FaPencilAlt } from "react-icons/fa";
import { LuTestTubeDiagonal } from "react-icons/lu";
import { BiMoneyWithdraw } from "react-icons/bi";
import { HiMiniMagnifyingGlass } from "react-icons/hi2";
import { BsFillFileBarGraphFill } from "react-icons/bs";
import { useState } from 'react';
import { useItems } from '../../hooks/useItems';

export const ItemForm = ({ onClose }) => {

    const { createItem, isCreating, materiaPrima } = useItems();

    const [activeTab, setActiveTab] = useState('basico');
    // const [loadingItems, setLoadingItems] = useState(false);
    const [errors, setErrors] = useState({});
    // const [isEditing, setIsEditing] = useState(false);

    const inputClasses = "w-full bg-white text-sm px-2 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 ease-in-out placeholder-gray-300 text-gray-800";
    const labelClasses = "block text-sm font-semibold text-gray-700 mb-1";
    // const errorClasses = "text-red-500 text-xs mt-1";

    const tabs = [
        { id: 'basico', label: 'Información Básica', icon: <MdArticle /> },
        { id: 'propiedades', label: 'Propiedades', icon: <FaMicroscope /> },
        { id: 'formulaciones', label: 'Formulaciones', icon: <LuTestTubeDiagonal /> },
        { id: 'costos', label: 'Inventario & Costos', icon: <BiMoneyWithdraw /> }
    ];

    const [formData, setFormData] = useState({
        nombre: '',
        codigo: '',
        tipo: 'PRODUCTO',
        categoria_id: '1',
        unidad_id: '1',
        viscosidad: '',
        p_g: '',
        color: '',
        brillo_60: '',
        secado: '',
        cubrimiento: '',
        molienda: '',
        ph: '',
        poder_tintoreo: '',
        volumen: '',
        cantidad: 0,
        costo_unitario: 0,
        bodega_id: '1',
        envase: 0,
        etiqueta: 0,
        plastico: 0
    });

    const [formulaciones, setFormulaciones] = useState([]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const addMateriaPrima = () => {
        const nueva = { 
            id: Date.now(), 
            id_item_general: '', 
            cantidad: 0
        };
        setFormulaciones([...formulaciones, nueva]);
    };

    const removeMateriaPrima = (id) => {
        setFormulaciones(formulaciones.filter(f => f.id !== id));
    };

    const handleFormuChange = (id, field, value) => {
        setFormulaciones(formulaciones.map(f => 
            f.id === id ? { ...f, [field]: value } : f
        ));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        
        const listaProcesada = formulaciones
            .map(f => ({
                // Mapeamos id_item_general -> materia_prima_id
                materia_prima_id: parseInt(f.id_item_general), 
                cantidad: parseFloat(f.cantidad || 0),
                porcentaje: parseFloat(f.porcentaje || 0)
            }))
            // Ahora el filtro sí encontrará el ID y no borrará la lista
            .filter(f => !isNaN(f.materia_prima_id) && f.materia_prima_id > 0);

        const payload = {
            ...formData,
            categoria_id: parseInt(formData.categoria_id),
            unidad_id: parseInt(formData.unidad_id),
            bodega_id: parseInt(formData.bodega_id),
            costo_unitario: parseFloat(formData.costo_unitario || 0),
            cantidad: parseFloat(formData.cantidad || 0),
            envase: parseFloat(formData.envase || 0),
            etiqueta: parseFloat(formData.etiqueta || 0),
            plastico: parseFloat(formData.plastico || 0),
            formulaciones: listaProcesada 
        };

        console.log("Payload corregido:", payload);

        createItem(payload, {
            onSuccess: () => {
                alert("¡Item y Receta creados con éxito!");
                onClose();
            },
            onError: (err) => setErrors({ server: err.message })
        });
    };

    const renderTabContent = () => {
        switch (activeTab) {
            case 'basico':
                return (
                    <div className="space-y-6">
                        <div className="bg-blue-200 p-6 rounded-lg">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className={labelClasses}>Nombre *</label>
                                    <input type="text" name="nombre" value={formData.nombre} onChange={handleChange} className={inputClasses} placeholder="Ej: Esmalte Blanco" />
                                </div>
                                <div>
                                    <label className={labelClasses}>Código *</label>
                                    <input type="text" name="codigo" value={formData.codigo} onChange={handleChange} className={inputClasses} placeholder="Ej: EBT001" />
                                </div>
                                <div>
                                    <label className={labelClasses}>Tipo *</label>
                                    <select name="tipo" value={formData.tipo} onChange={handleChange} className={inputClasses}>
                                        <option value="PRODUCTO">PRODUCTO</option>
                                        <option value="MATERIA PRIMA">MATERIA PRIMA</option>
                                        <option value="INSUMO">INSUMO</option>
                                    </select>
                                </div>
                                <div>
                                    <label className={labelClasses}>Categoría *</label>
                                    <select name="categoria_id" value={formData.categoria_id} onChange={handleChange} className={inputClasses}>
                                        <option value="1">ESMALTE</option>
                                        <option value="2">PASTA</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                );
            
            case 'propiedades':
                return (
                    <div className="space-y-6">
                        <div className="bg-blue-200 p-6 rounded-lg">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {['viscosidad', 'p_g', 'color', 'brillo_60', 'secado', 'cubrimiento', 'molienda', 'ph', 'poder_tintoreo', 'volumen'].map((campo) => (
                                    <div key={campo}>
                                        <label className={labelClasses}>{campo.toUpperCase()}</label>
                                        <input type="text" name={campo} value={formData[campo]} onChange={handleChange} className={inputClasses} />
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                );

            case 'costos':
                return (
                    <div className="space-y-4">
                        <div className="bg-blue-200 p-6 rounded-lg grid grid-cols-2 gap-6">
                            <div>
                                <label className={labelClasses}>Cantidad Inicial</label>
                                <input type="number" name="cantidad" value={formData.cantidad} onChange={handleChange} className={inputClasses} />
                            </div>
                            <div>
                                <label className={labelClasses}>Costo Unitario</label>
                                <input type="number" name="costo_unitario" value={formData.costo_unitario} onChange={handleChange} className={inputClasses} />
                            </div>
                            {/* --- CAMPOS AGREGADOS PARA COINCIDIR CON POSTMAN --- */}
                            <div>
                                <label className={labelClasses}>Envase</label>
                                <input type="number" name="envase" value={formData.envase} onChange={handleChange} className={inputClasses} />
                            </div>
                            <div>
                                <label className={labelClasses}>Etiqueta</label>
                                <input type="number" name="etiqueta" value={formData.etiqueta} onChange={handleChange} className={inputClasses} />
                            </div>
                            <div>
                                <label className={labelClasses}>Plástico</label>
                                <input type="number" name="plastico" value={formData.plastico} onChange={handleChange} className={inputClasses} />
                            </div>
                        </div>
                    </div>
                );

            case 'formulaciones':
                return (
                    <div className="bg-blue-200 p-6 rounded-lg">
                        <div className="flex justify-between mb-4">
                            <h3 className="font-bold">Composición del Producto</h3>
                            <button type="button" onClick={addMateriaPrima} className="bg-green-600 text-white px-4 py-2 rounded flex items-center gap-2">
                                <MdAdd /> Agregar Materia Prima
                            </button>
                        </div>
                        {formulaciones.map((f) => (
                            <div key={f.id} className="bg-white p-4 rounded mb-2 flex gap-4 items-end shadow-sm">
                                <div className="flex-1">
                                    <label className="text-xs font-bold">Materia Prima</label>
                                    <select 
                                        value={f.id_item_general} 
                                        onChange={(e) => {
                                            const val = e.target.value;
                                            console.log("ID Seleccionado:", val, f.id_item_general);
                                            handleFormuChange(f.id, 'id_item_general', val);
                                        }}
                                        className={inputClasses}
                                    >
                                        <option value="">Seleccione...</option>
                                        {materiaPrima?.map((mp, i) => (
                                            <option key={i} value={mp.id_item_general}>{mp.nombre} ({mp.codigo})</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="w-24">
                                    <label className="text-xs font-bold">Cant.</label>
                                    <input type="number" step="0.01" value={f.cantidad} onChange={(e) => handleFormuChange(f.id, 'cantidad', e.target.value)} className={inputClasses} />
                                </div>
                                <button onClick={() => removeMateriaPrima(f.id)} className="bg-red-500 text-white p-2 rounded">
                                    <MdDelete />
                                </button>
                            </div>
                        ))}
                    </div>
                );
            default: return null;
        }
    };

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-hidden">
                <form onSubmit={handleSubmit}>
                    <div className="bg-blue-500 text-white p-6 flex justify-between items-center">
                        <h2 className="text-2xl font-bold flex items-center gap-2">
                            { /* <MdAddCircleOutline size={28} /> {isEditing ? 'Editar Item' : 'Crear Nuevo Item'} */}
                            <MdAddCircleOutline size={28} /> Crear Nuevo Item
                        </h2>
                        <button type="button" onClick={onClose}><MdClose size={28} /></button>
                    </div>
                    <div className="flex border-b bg-gray-50">
                        {tabs.map(tab => (
                            <button key={tab.id} type="button" onClick={() => setActiveTab(tab.id)}
                                className={`px-6 py-4 flex items-center gap-2 ${activeTab === tab.id ? 'border-b-2 border-blue-600 text-blue-600 bg-white' : ''}`}>
                                {tab.icon} {tab.label}
                            </button>
                        ))}
                    </div>
                    <div className="p-6 overflow-y-auto max-h-[60vh]">
                        {renderTabContent()}
                        {errors.server && <p className="text-red-500 mt-4 text-center font-bold">{errors.server}</p>}
                    </div>
                    <div className="p-4 bg-gray-100 flex justify-end gap-3">
                        <button type="button" onClick={onClose} className="px-6 py-2 border rounded-lg">Cancelar</button>
                        <button type="submit" disabled={isCreating} className="px-6 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2">
                            {/* <MdSave /> {isCreating ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Guardar Item')} */}
                            <MdSave /> {isCreating ? 'Guardando...' : 'Guardar Item'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};