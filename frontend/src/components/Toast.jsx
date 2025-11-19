import { FaCheck, FaRegWindowClose } from "react-icons/fa";
import { IoWarningOutline } from "react-icons/io5";
import { useEffect, useState } from "react";

export const Toast = ({ message, type = "info", onClose }) => {
    console.log("🔥 Toast:", message, type);
    const [animate, setAnimate] = useState(false);

    const colors = {
        success: "bg-green-100 text-green-800",
        error: "bg-red-100 text-red-800",
        warning: "bg-yellow-100  text-yellow-800",
        info: "bg-blue-100 text-blue-800",
    };

    const iconWrapperColors = {
        success: "bg-green-300",
        error: "bg-red-200",
        warning: "bg-yellow-200",
        info: "bg-blue-200",
    };

    const iconColor = {
        success: "text-green-700",
        error: "text-red-700",
        warning: "text-yellow-700",
        info: "text-blue-700",
    };

    useEffect(() => {
        const entryTimer = setTimeout(() => setAnimate(true), 20);
        const exitTimer = setTimeout(() => {
            setAnimate(false);
            setTimeout(onClose, 200);
        }, 2000);

        return () => {
            clearTimeout(entryTimer);
            clearTimeout(exitTimer);
        };
    }, [onClose]);

    return (
        <div
            className={`
                flex items-center w-full max-w-sm px-4 py-3 rounded-xl shadow-xl/20
                fixed bottom-6 right-6 z-50 transition-all duration-300 transform
                ${colors[type] || colors.info}
                ${animate ? "opacity-100 translate-y-0 scale-100" : "opacity-0 translate-y-3 scale-95"}
            `}
        >
            <div
                className={`flex items-center justify-center w-8 h-8 rounded-full mr-3
                ${iconWrapperColors[type] || iconWrapperColors.info}`}
            >
                {type === "success" && <FaCheck className={`w-4 h-4 ${iconColor[type]}`} />}
                {type === "error" && <FaRegWindowClose className={`w-4 h-4 ${iconColor[type]}`} />}
                {type === "warning" && <IoWarningOutline className={`w-4 h-4 ${iconColor[type]}`} />}
                {type === "info" && <FaInfoCircle  className={`w-4 h-4 ${iconColor[type]}`} />}
            </div>

            <p className="text-sm font-medium leading-tight">
                {message}
            </p>
        </div>
    );
};
