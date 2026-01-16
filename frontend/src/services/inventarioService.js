export const generateCode = () => {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return Array.from({ length: 6 }, () => 
        characters.charAt(Math.floor(Math.random() * characters.length))
    ).join('');
};

export const mapItemDetailToForm = (detail, idBodega) => {
    if (!detail) return null;

    return {
        formData: {
            nombre: detail.nombre || '',
            codigo: detail.codigo || '',
            tipo: detail.tipo || '',
            categoria_id: detail.categoria_id || '',
            viscosidad: detail.viscosidad || '',
            p_g: detail.p_g || '',
            color: detail.color || '',
            brillo_60: detail.brillo_60 || '',
            secado: detail.secado || '',
            cubrimiento: detail.cubrimiento || '',
            molienda: detail.molienda || '',
            ph: detail.ph || '',
            poder_tintoreo: detail.poder_tintoreo || '',
            cantidad: detail.cantidad || 0,
            costo_unitario: detail.costo_unitario || 0,
            bodega_id: detail.bodega_id || idBodega || '',
            envase: detail.envase || 0,
            etiqueta: detail.etiqueta || 0,
            plastico: detail.plastico || 0
        },
        formulaciones: (detail.formulaciones || []).map(f => ({
            id: f.id_item_general_formulaciones || Date.now() + Math.random(),
            id_item_general: String(f.item_general_id),
            nombre: f.nombre,
            cantidad: f.cantidad
        }))
    };
};

export const prepareItemPayload = (formData, formulacionesList) => {
    const isMateriaPrima = formData.tipo === '1';

    const formulacionesProcesadas = isMateriaPrima 
        ? [] 
        : formulacionesList
            .map(f => ({
                materia_prima_id: parseInt(f.id_item_general),
                cantidad: parseFloat(f.cantidad || 0),
            }))
            .filter(f => !isNaN(f.materia_prima_id) && f.materia_prima_id > 0);

    return {
        ...formData,
        categoria_id: parseInt(formData.categoria_id || 0),
        bodega_id: parseInt(formData.bodega_id || 0),
        costo_unitario: parseFloat(formData.costo_unitario || 0),
        cantidad: parseFloat(formData.cantidad || 0),
        envase: parseFloat(formData.envase || 0),
        etiqueta: parseFloat(formData.etiqueta || 0),
        plastico: parseFloat(formData.plastico || 0),
        formulaciones: formulacionesProcesadas
    };
};