import React, {useRef} from "react";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faCheckCircle, faExclamationTriangle, faTimes} from "@fortawesome/free-solid-svg-icons";

export default function Alerta({alerta, setAlerta, alertaRef}) {
    const ref = useRef();

    const alertaIcone = () => {
        switch (alerta.tipo) {
            case "success":
                return <FontAwesomeIcon icon={faCheckCircle} className="mx-2"/>
            case "danger":
                return <FontAwesomeIcon icon={faExclamationTriangle} className="mx-2"/>
            case "warning":
                return <FontAwesomeIcon icon={faExclamationTriangle} className="mx-2"/>
            default:
                return "";
        }
    }

    return (
        <div ref={alertaRef ?? ref} className={alerta.mensagem ? `alert alert-${alerta.tipo}` : ""}
             style={{display: "flex", justifyContent: "space-between"}}>
            <div>
                {alertaIcone()}
                {alerta.mensagem}
            </div>

            <div style={alerta.mensagem ? {cursor: "pointer"} : {display: "none"}}
                 onClick={() => setAlerta({tipo: "", mensagem: ""})}>
                <FontAwesomeIcon icon={faTimes}/>
            </div>
        </div>
    )
}
