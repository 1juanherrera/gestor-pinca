import { MdSave, MdClose, MdAdd, MdDelete, MdAddCircleOutline, MdArticle } from 'react-icons/md';
import { FaMicroscope, FaPencilAlt } from "react-icons/fa";
import { LuTestTubeDiagonal } from "react-icons/lu";
import { BiMoneyWithdraw } from "react-icons/bi";
import { HiMiniMagnifyingGlass } from "react-icons/hi2";
import { BsFillFileBarGraphFill } from "react-icons/bs";
import { useState } from 'react';

export const ItemForm = ({ onClose }) => {

    const [activeTab, setActiveTab] = useState('basico');
    const [tipo, setTipo] = useState('PRODUCTO');
    const [formulaciones, setFormulaciones] = useState([]);
    const [materiasPrimas, setMateriasPrimas] = useState([]);
    const [loadingItems, setLoadingItems] = useState(false);
    const [errors, setErrors] = useState({});
    const [isEditing, setIsEditing] = useState(false);

    const inputClasses = "w-full bg-white text-sm px-2 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150 ease-in-out placeholder-gray-300 text-gray-800";
    const labelClasses = "block text-sm font-semibold text-gray-700 mb-1";
    const errorClasses = "text-red-500 text-xs mt-1";

    const tabs = [
        { id: 'basico', label: 'Información Básica', icon: <MdArticle /> },
        { id: 'propiedades', label: 'Propiedades', icon: <FaMicroscope /> },
        { id: 'formulaciones', label: 'Formulaciones', icon: <LuTestTubeDiagonal /> },
        { id: 'costos', label: 'Inventario & Costos', icon: <BiMoneyWithdraw /> }
    ];


    const renderTabContent = () => {
        switch (activeTab) {
            case 'basico':
                return (
                    <div className="space-y-6">
                        <div className="bg-blue-200 shadow-lg shadow-blue-50 p-6 rounded-lg">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className={labelClasses}>
                                        Nombre <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="nombre"
                                        className={`${inputClasses} ${errors.nombre ? 'border-red-500' : ''}`}
                                        placeholder="Ej: Esmalte Blanco"
                                    />
                                    {errors.nombre && <p className={errorClasses}>{errors.nombre}</p>}
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>
                                        Código <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="codigo"
                                        className={`${inputClasses} ${errors.codigo ? 'border-red-500' : ''}`}
                                        placeholder="Ej: PAD131, MCP852"
                                    />
                                    {errors.codigo && <p className={errorClasses}>{errors.codigo}</p>}
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>
                                        Tipo <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        name="tipo"
                                        className={`${inputClasses} ${errors.tipo ? 'border-red-500' : ''}`}
                                    >
                                        <option value="PRODUCTO">PRODUCTO</option>
                                        <option value="MATERIA PRIMA">MATERIA PRIMA</option>
                                        <option value="INSUMO">INSUMO</option>
                                    </select>
                                    {errors.tipo && <p className={errorClasses}>{errors.tipo}</p>}
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>
                                        Categoría <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        name="categoria_id"
                                        className={`${inputClasses} ${errors.tipo ? 'border-red-500' : ''}`}
                                    >
                                        <option value="1">ESMALTE</option>
                                        <option value="2">PASTA</option>
                                        <option value="3">ANTICORROSIVO</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                );
            
            case 'propiedades':
                return (
                    <div className="space-y-6">
                        <div className="bg-blue-200 shadow-lg shadow-blue-50 p-6 rounded-lg">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label className={labelClasses}>Viscosidad</label>
                                    <input type="text" name="viscosidad" className={inputClasses} placeholder="Ej: 85-95 KU" />
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>P.G (Peso Galón)</label>
                                    <input type="text" name="p_g" className={inputClasses} placeholder="Ej: 3,6+/-0,05 Kg" />
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>Color</label>
                                    <input type="text" name="color" className={inputClasses} placeholder="Ej: Blanco, Rojo, etc." />
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>Brillo 60°</label>
                                    <input type="text" name="brillo_60" className={inputClasses} placeholder="Ej: >=90" />
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>Tiempo de Secado</label>
                                    <input type="text" name="secado" className={inputClasses} placeholder="Ej: 30 min" />
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>Cubrimiento</label>
                                    <input type="text" name="cubrimiento" className={inputClasses} placeholder="Ej: 100+/-5 %" />
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>Molienda</label>
                                    <input type="text" name="molienda" className={inputClasses} placeholder="Ej: 7.5 H" />
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>pH</label>
                                    <input type="text" name="ph" className={inputClasses} placeholder="Ej: 8.5" />
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>Poder Tintóreo</label>
                                    <input type="text" name="poder_tintoreo" className={inputClasses} placeholder="Ej: 95%" />
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>Volumen</label>
                                    <input type="text" name="volumen" className={inputClasses} placeholder="Ej: 200" />
                                </div>
                            </div>
                        </div>
                    </div>
                );
            
            case 'costos':
                return (
                    <div className="space-y-6">
                        <div className="bg-blue-200 shadow-blue-50 p-6 rounded-lg">
                            <h3 className="text-lg font-semibold mb-4">Inventario y Costos</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className={labelClasses}>Cantidad en Inventario</label>
                                    <input
                                        type="number"
                                        name="cantidad"
                                        className={inputClasses}
                                        placeholder="0"
                                    />
                                    <p className="text-xs text-gray-500 mt-1">Cantidad actual en inventario</p>
                                </div>
                                
                                <div>
                                    <label className={labelClasses}>Costo Unitario</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        name="costo_unitario"
                                        className={inputClasses}
                                        placeholder="0.00"
                                    />
                                    <p className="text-xs text-gray-500 mt-1">Costo por unidad en moneda local</p>
                                </div>
                            </div>
                        </div>
                    </div>
                );
            
            case 'formulaciones':
                return (
                    <div className="space-y-6">
                        <div className="p-6 rounded-lg shadow-lg shadow-blue-100 bg-blue-200">
                            <div className="flex justify-between items-center mb-4">
                                <h3 className="text-lg font-semibold">Base de fabricación</h3>
                                {(tipo === 'PRODUCTO' || tipo === 'INSUMO') && (
                                    <button
                                        type="button"
                                        className="bg-green-600 cursor-pointer text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2"
                                        disabled={loadingItems}
                                    >
                                        <MdAdd size={18} />
                                        Agregar
                                    </button>
                                )}
                            </div>

                            {(tipo === 'PRODUCTO' || tipo === 'INSUMO') ? (
                                <>
                                    {/* Debug info */}
                                    <div className="mb-4 p-3 bg-gray-800 rounded text-sm text-white flex gap-1">
                                        <HiMiniMagnifyingGlass size={18}/>
                                        <p> {loadingItems ? 'Cargando materias primas...' : `${materiasPrimas.length} materias primas disponibles`}</p>
                                        <div className="ml-5 flex gap-1">
                                            <BsFillFileBarGraphFill size={18}/>
                                        <p>Total items cargados: {materiasPrimas.length}</p>
                                        </div>
                                    </div>

                                    {formulaciones.length === 0 ? (
                                        <div className="text-center py-12 flex flex-col items-center text-gray-500">
                                            <div className="text-6xl mb-4"><LuTestTubeDiagonal alignmentBaseline='center'/></div>
                                            <p className="text-gray-500 text-lg">No hay formulaciones agregadas</p>
                                            <p className="text-gray-400 text-sm">Agrega una formulación para empezar</p>
                                        </div>
                                    ) : (
                                        <div className="space-y-4">
                                            {formulaciones.map((formulacion, index) => (
                                                <div key={formulacion.id} className="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                                    <div className="flex justify-between items-start mb-4">
                                                        <h4 className="font-medium text-gray-700">Materia prima #{index + 1}</h4>
                                                        <button
                                                            type="button"
                                                            className="bg-red-500 cursor-pointer text-white px-3 py-1 rounded hover:bg-red-600 flex items-center gap-1"
                                                        >
                                                            <MdDelete size={14} />
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                    
                                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                        <div>
                                                            <label className={labelClasses}>Materia Prima</label>
                                                            <select
                                                                value={formulacion.materia_prima_id}
                                                                className={`${inputClasses} ${errors[`formulacion_${index}_materia`] ? 'border-red-500' : ''}`}
                                                                disabled={loadingItems}
                                                            >
                                                                <option value="">
                                                                    {loadingItems ? 'Cargando...' : 'Seleccione...'}
                                                                </option>
                                                                {materiasPrimas.map(mp => (
                                                                    <option key={mp.id} value={mp.id}>
                                                                        {mp.codigo} - {mp.nombre}
                                                                    </option>
                                                                ))}
                                                            </select>
                                                            {errors[`formulacion_${index}_materia`] && (
                                                                <p className={errorClasses}>{errors[`formulacion_${index}_materia`]}</p>
                                                            )}
                                                        </div>
                                                        
                                                        <div>
                                                            <label className={labelClasses}>Cantidad</label>
                                                            <input
                                                                type="number"
                                                                step="0.001"
                                                                value={formulacion.cantidad}
                                                                className={`${inputClasses} ${errors[`formulacion_${index}_cantidad`] ? 'border-red-500' : ''}`}
                                                                placeholder="0.000"
                                                            />
                                                            {errors[`formulacion_${index}_cantidad`] && (
                                                                <p className={errorClasses}>{errors[`formulacion_${index}_cantidad`]}</p>
                                                            )}
                                                        </div>
                                                        
                                                        <div>
                                                            <label className={labelClasses}>Unidad</label>
                                                            <select
                                                                value={formulacion.unidad}
                                                                className={`${inputClasses} ${errors[`formulacion_${index}_unidad`] ? 'border-red-500' : ''}`}
                                                            >
                                                                <option value="">Seleccione...</option>
                                                                <option value="kg">Kilogramos (kg)</option>
                                                                <option value="g">Gramos (g)</option>
                                                                <option value="l">Litros (l)</option>
                                                                <option value="ml">Mililitros (ml)</option>
                                                                <option value="unidad">Unidades</option>
                                                            </select>
                                                            {errors[`formulacion_${index}_unidad`] && (
                                                                <p className={errorClasses}>{errors[`formulacion_${index}_unidad`]}</p>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </>
                            ) : (
                                <div className="text-center py-12">
                                    <div className="text-6xl mb-4">🚫</div>
                                    <p className="text-gray-500 text-lg">Formulaciones no disponibles</p>
                                    <p className="text-gray-400 text-sm">Las formulaciones solo están disponibles para productos e insumos</p>
                                </div>
                            )}
                        </div>
                    </div>
                );
            
            default:
                return null;
        }
    };

    return (
        <div onClick={onClose}
            className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div 
            onClick={e => e.stopPropagation()}
            className="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-hidden">
                {/* Header */}
                <div className="bg-blue-500 text-white p-6 flex justify-between items-center">
                    <div className='flex items-center gap-2'>
                    <h2 className="text-2xl font-bold flex items-center gap-2">
                        {isEditing ? (
                            <>
                            <FaPencilAlt />
                            Editar Item
                            </>
                        ) : (
                            <>
                                <MdAddCircleOutline size={28} />
                                Crear Nuevo Item
                            </>
                        )}
                    </h2>
                    </div>
                    <button
                        onClick={onClose}
                        type="button"
                        className="text-white  hover:text-gray-200 transition-colors cursor-pointer"
                    >
                        <MdClose size={28} />
                    </button>
                </div>

                {/* Tabs */}
                <div className="bg-gray-50 border-b border-gray-200">
                    <div className="flex space-x-0 overflow-x-auto">
                        {tabs.map((tab) => (
                            <button
                                key={tab.id}
                                type="button"
                                onClick={() => setActiveTab(tab.id)}
                                className={`shrink-0 cursor-pointer px-6 py-4 font-medium text-sm transition-all flex items-center ${
                                    activeTab === tab.id
                                        ? 'bg-white text-blue-600 border-b-2 border-blue-600'
                                        : 'text-gray-600 hover:text-gray-800 hover:bg-gray-100'
                                }`}
                            >
                                <span className="mr-2">{tab.icon}</span>
                                {tab.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Content */}
                <form>
                    <div className="p-6 overflow-y-auto max-h-[calc(95vh-200px)]">
                        {renderTabContent()}
                    </div>

                    {/* Footer */}
                    <div className="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-4">
                        <button
                            type="button"
                            className="px-6 py-2 cursor-pointer text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            className="px-8 py-2 cursor-pointer bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                            disabled={loadingItems}
                        >
                            <MdSave size={18} />
                            {isEditing ? 'Actualizar' : 'Crear'} Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};