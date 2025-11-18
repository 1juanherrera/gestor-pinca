import { useState } from "react";


export const useToast = () => {

    const [toastVisible, setToastVisible] = useState(false);
    const [toastMessage, setToastMessage] = useState("");
    const [toastType, setToastType] = useState("info");

    const eventToast = (message, type = "info") => {
        setToastMessage(message);
        setToastType(type);
        setToastVisible(true);
    }

    return {
        toastVisible,
        toastMessage,
        toastType,

        eventToast,
        setToastVisible
    }
}