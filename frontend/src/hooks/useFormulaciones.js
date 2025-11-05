import { useState } from "react";
import { apiRequest, useApiResource } from "../Connection/getApi";

export const useFormulaciones = () => {

  const query = useApiResource('/formulaciones');
  const data = query.data ?? [];

  const productos = data.filter(item => item.tipo === 'PRODUCTO');
  const insumos = data.filter(item => item.tipo === 'INSUMO');

  const [selectedProduct, setSelectedProduct] = useState(null);
  const [recalculatedData, setRecalculatedData] = useState(null);
  const [isRecalculating, setIsRecalculating] = useState(false);
  const [nuevoVolumen, setNuevoVolumen] = useState("");

  const productDetailQuery = useApiResource(
    selectedProduct ? `/formulaciones/costos/${selectedProduct}` : null,
    selectedProduct ? `formulaciones-${selectedProduct}` : null,
    "Error al obtener detalle de formulación"
  );

  const recalculate = async (newVolume) => {
    if (!selectedProduct || !newVolume) return null;

    try {
      setIsRecalculating(true);

      const data = await apiRequest({
        method: "GET",
        endpoint: `/formulaciones/recalcular_costos/${selectedProduct}/${newVolume}`,
        errorMsg: "Error al recalcular costos",
      });

      setRecalculatedData(data);
      return data;
    } catch (error) {
      console.error("❌ Error recalculando:", error);
      throw error;
    } finally {
      setIsRecalculating(false);
    }
  };

  const handleProductSelect = (productId) => {
    setSelectedProduct(productId);
  }

  const handleClearSelection = () => {
    setSelectedProduct('');
  }

  const handleRecalcular = async () => {
    if (!nuevoVolumen) return alert("Ingresa un nuevo volumen");
    await recalculate(nuevoVolumen);
  };

  return {
    ...query,
    productos,
    insumos,
    selectedProduct,
    setSelectedProduct,
    productDetail: productDetailQuery.data,
    loadingDetail: productDetailQuery.isLoading,
    refreshDetail: productDetailQuery.refetch,
    handleProductSelect,
    handleClearSelection,

    handleRecalcular,
    setNuevoVolumen,
    recalculatedData,
    isRecalculating,
  };
};
