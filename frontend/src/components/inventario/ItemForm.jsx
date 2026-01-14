import { MdSave, MdClose, MdAdd, MdDelete, MdAddCircleOutline, MdArticle } from 'react-icons/md';
import { FaMicroscope } from "react-icons/fa";
import { LuTestTubeDiagonal } from "react-icons/lu";
import { BiMoneyWithdraw } from "react-icons/bi";
import { useState } from 'react';
import { useItems } from '../../hooks/useItems';
import { CustomSelect } from '../CustomSelect';

export const ItemForm = ({ onClose, idBodega }) => {

    const { createItem, isCreating, materiaPrima } = useItems();

    const [activeTab, setActiveTab] = useState('basico');
    // const [loadingItems, setLoadingItems] = useState(false);
    const [errors, setErrors] = useState({});
    // const [isEditing, setIsEditing] = useState(false);

    const inputClasses = "w-full bg-white text-sm px-3 py-[8px] border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 ease-in-out placeholder-gray-300 text-gray-800 shadow-sm";
    const labelClasses = "block text-sm font-semibold text-gray-700 mb-1 uppercase";
    // const errorClasses = "text-red-500 text-xs mt-1";

    const tabs = [
        { id: 'basico', label: 'Información Básica', icon: <MdArticle /> },
        { id: 'propiedades', label: 'Propiedades', icon: <FaMicroscope /> },
        { id: 'formulaciones', label: 'Formulaciones', icon: <LuTestTubeDiagonal /> },
        { id: 'costos', label: 'Inventario & Costos', icon: <BiMoneyWithdraw /> }
    ];

    const opcionesTipo = [
        { value: 'PRODUCTO', label: 'PRODUCTO' },
        { value: 'MATERIA PRIMA', label: 'MATERIA PRIMA' },
        { value: 'INSUMO', label: 'INSUMO' }
    ];

    const opcionesCategoria =[
        { value: '1', label: 'ESMALTE' },
        { value: '2', label: 'PASTA' },
        { value: '3', label: 'ANTICORROSIVO' },
        { value: '4', label: 'BARNIZ' },
    ]

    const propiedadesFisicas = [
        { id: 'viscosidad', label: 'Viscosidad' },
        { id: 'p_g', label: 'Densidad / P.G.' },
        { id: 'color', label: 'Color' },
        { id: 'brillo_60', label: 'Brillo (60°)' },
        { id: 'secado', label: 'Tiempo de Secado' },
        { id: 'cubrimiento', label: 'Poder Cubriente' },
        { id: 'molienda', label: 'Molienda / Finura' },
        { id: 'ph', label: 'pH' },
        { id: 'poder_tintoreo', label: 'Poder Tintóreo' }
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
        cantidad: 0,
        costo_unitario: 0,
        bodega_id: idBodega || '',
        envase: 0,
        etiqueta: 0,
        plastico: 0
    });

    const [formulaciones, setFormulaciones] = useState([]);

    const materiaPrimaOptions = materiaPrima?.map(mp => ({
    value: mp.id_item_general,
    label: `${mp.nombre} (${mp.codigo})`
    })) || [];

    const handleFocus = (e) => {
        if (e.target.value === '0') {
            e.target.select();
        }
    };

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

        console.log("Formulaciones Snapshot:", JSON.parse(JSON.stringify(formulaciones)));

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
                                    <label className={labelClasses}>NOMBRE <span className="text-red-700">*</span></label>
                                    <input type="text" name="nombre" value={formData.nombre} onChange={handleChange} className={inputClasses} />
                                </div>
                                <div>
                                    <label className={labelClasses}>CÓDIGO <span className="text-red-700">*</span></label>
                                    <input type="text" name="codigo" value={formData.codigo} onChange={handleChange} className={inputClasses} />
                                </div>
                                <CustomSelect 
                                    label="TIPO"
                                    name="tipo"
                                    value={formData.tipo}
                                    options={opcionesTipo}
                                    onChange={handleChange}
                                    isRequired={true}
                                />
                                <CustomSelect 
                                    label="CATEGORÍA"
                                    name="categoria_id"
                                    value={formData.categoria_id}
                                    options={opcionesCategoria}
                                    onChange={handleChange}
                                    isRequired={true}
                                />
                            </div>
                        </div>
                    </div>
                );
            
            case 'propiedades':
                return (
                    <div className="space-y-6">
                        <div className="bg-blue-200 p-6 rounded-lg">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {propiedadesFisicas.map((campo) => (
                                    <div key={campo.id}>
                                        <label className={labelClasses}>{campo.label.toUpperCase()}</label>
                                        
                                        <input 
                                            type="text" 
                                            name={campo.id}
                                            value={formData[campo.id]} 
                                            onChange={handleChange} 
                                            className={inputClasses}
                                        />
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
                                <input 
                                    type="number" 
                                    name="cantidad" 
                                    value={formData.cantidad} 
                                    onChange={handleChange} 
                                    onFocus={handleFocus}
                                    className={inputClasses} />
                            </div>
                            <div>
                                <label className={labelClasses}>Costo Unitario</label>
                                <input 
                                    type="number" 
                                    name="costo_unitario" 
                                    value={formData.costo_unitario} 
                                    onChange={handleChange} 
                                    onFocus={handleFocus}
                                    className={inputClasses} />
                            </div>
                            {/* --- CAMPOS AGREGADOS PARA COINCIDIR CON POSTMAN --- */}
                            <div>
                                <label className={labelClasses}>Envase</label>
                                <input 
                                    type="number" 
                                    name="envase" 
                                    value={formData.envase} 
                                    onChange={handleChange} 
                                    onFocus={handleFocus}
                                    className={inputClasses} />
                            </div>
                            <div>
                                <label className={labelClasses}>Etiqueta</label>
                                <input 
                                    type="number" 
                                    name="etiqueta" 
                                    value={formData.etiqueta} 
                                    onChange={handleChange} 
                                    onFocus={handleFocus}
                                    className={inputClasses} />
                            </div>
                            <div>
                                <label className={labelClasses}>Plástico</label>
                                <input 
                                    type="number" 
                                    name="plastico" 
                                    value={formData.plastico} 
                                    onChange={handleChange} 
                                    onFocus={handleFocus}
                                    className={inputClasses} />
                            </div>
                        </div>
                    </div>
                );

            case 'formulaciones':
                return (
                    <div className="bg-blue-200 p-6 rounded-lg">
                        <div className="flex justify-between mb-4">
                            <h3 className="font-bold uppercase text-gray-500 text-[14px]">Formulación del Producto</h3>
                            <button type="button" onClick={addMateriaPrima} className="bg-green-600 hover:bg-green-700 duration-200 cursor-pointer text-white px-4 py-2 rounded flex items-center gap-2">
                                <MdAdd size={24}/> Agregar Materia Prima
                            </button>
                        </div>
                        {formulaciones.map((f) => (
                            <div key={f.id} className="bg-white p-4 rounded mb-2 flex gap-4 items-end shadow-sm">
                                <div className="flex-3">
                                    <label className="text-xs font-bold text-gray-700 uppercase">Materia Prima</label>
                                <CustomSelect
                                    options={materiaPrimaOptions}
                                    value={materiaPrimaOptions.find(opt => opt.value === f.id_item_general)}
                                    onChange={(selected) => 
                                        handleFormuChange(f.id, 'id_item_general', selected ? selected.value : '')
                                    }
                                    placeholder="BUSCAR MATERIA PRIMA..."
                                />
                                </div>
                                
                                <div className="w-30">
                                    <label className="text-xs font-bold text-gray-700 uppercase">Cantidad</label>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        value={f.cantidad} 
                                        onFocus={handleFocus}
                                        onChange={(e) => handleFormuChange(f.id, 'cantidad', e.target.value)} 
                                        className={`${inputClasses} text-gray-100`} 
                                    />
                                </div>

                                <button 
                                    type="button"
                                    onClick={() => removeMateriaPrima(f.id)} 
                                    className="bg-red-500 duration-200 transform cursor-pointer text-white p-2.5 rounded-lg hover:bg-red-700"
                                >
                                    <MdDelete size={20} />
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
                        <button type="button" className="cursor-pointer" onClick={onClose}><MdClose size={28} /></button>
                    </div>
                    <div className="flex border-b border-gray-300 bg-gray-50">
                        {tabs.map(tab => (
                            <button key={tab.id} type="button" onClick={() => setActiveTab(tab.id)}
                                className={`px-6 py-4 cursor-pointer flex items-center gap-2 ${activeTab === tab.id ? 'border-b-3 bg-blue-100 border-blue-600 font-semibold text-blue-800' : 'text-gray-700'}`}>
                                {tab.icon} {tab.label}
                            </button>
                        ))}
                    </div>
                    <div className="p-6 overflow-y-auto max-h-[60vh]">
                        {renderTabContent()}
                        {errors.server && <p className="text-red-500 mt-4 text-center font-bold">{errors.server}</p>}
                    </div>
                    <div className="p-4 border-t border-gray-300 flex justify-end gap-3">
                        <button type="button" onClick={onClose} className="px-6 py-2 duration-200 transform cursor-pointer hover:scale-105 border border-gray-400 shadow-md rounded-lg">Cancelar</button>
                        <button type="submit" disabled={isCreating} className="px-6 duration-200 transform cursor-pointer hover:scale-105 py-2 bg-blue-600 hover:bg-blue-700 text-white shadow-md rounded-lg flex items-center gap-2">
                            {/* <MdSave /> {isCreating ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Guardar Item')} */}
                            <MdSave /> {isCreating ? 'Guardando...' : 'Guardar Item'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};