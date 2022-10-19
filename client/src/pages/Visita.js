import React, {useEffect, useState} from "react";

import useQuery from "../hooks/useQuery";

import DadosVisitante from "../components/DadosVisitante";
import DadosVisita from "../components/DadosVisita";
import RegistroVisita from "../components/RegistroVisita";
import FotoVisitante from "../components/FotoVisitante";
import TituloVisita from "../components/TituloVisita";
import Alerta from "../components/Alerta";
import ModalVisita from "../components/ModalVisita";

export default function Visita() {
    const query = useQuery();

    const [id] = useState(query.get("id") ?? "");
    const [cpf, setCpf] = useState("");
    const [status, setStatus] = useState("");
    const [visita, setVisita] = useState({});
    const [visitante, setVisitante] = useState({});
    const [exibirModalFinalizar, setExibirModalFinalizar] = useState(false);
    const [exibirModalEditar, setExibirModalEditar] = useState(false);
    const [alerta, setAlerta] = useState({tipo: "", mensagem: ""});

    const onCpfValido = (codigoResposta, visitante) => {
        if (codigoResposta === 200) {
            setVisitante(visitante);
        }
    }

    const onVisitaEncontrada = (visita) => {
        setVisita(visita);
    }

    useEffect(() => {
        setCpf(visita.cpf);

        if(visita.finalizada_em) {
            setStatus("fechada");
        } else if(visita.data_visita) {
            setStatus("aberta");
        }
    }, [visita]);

    return (
        <div className="form-fieldset">
            <Alerta alerta={alerta} setAlerta={setAlerta}/>
            <TituloVisita status={status} id={id} onEditar={()=>setExibirModalEditar(true)} onFinalizar={()=>setExibirModalFinalizar(true)}/>
            <hr/>
            <form className="form" id='form' encType='multipart/form-data'>
                <div className="form-wrapper">
                    <div className="width--95">
                        <DadosVisitante estado="disabled" estadoCpf="disabled" cpf={cpf}
                                        onCpfValido={onCpfValido} buscarDados={!visitante.cpf}/>
                        <hr/>
                        <DadosVisita id={id} disabled={!exibirModalEditar} onVisitaEncontrada={onVisitaEncontrada} status={status}/>
                        <hr/>
                        <RegistroVisita
                            cadastradaEm={visita.data_visita}
                            cadastradaPor={visita.cadastrada_por}
                            modificadaEm={visita.modificada_em}
                            modificadaPor={visita.modificada_por}
                            finalizadaEm={visita.finalizada_em}
                            finalizadaPor={visita.finalizada_por}
                        />
                    </div>
                </div>
                <FotoVisitante foto={visitante.foto}/>
            </form>
            <ModalVisita
                editar={{exibirModalEditar: exibirModalEditar, setExibirModalEditar: setExibirModalEditar}}
                finalizar={{exibirModalFinalizar: exibirModalFinalizar, setExibirModalFinalizar: setExibirModalFinalizar}}
                visita={visita}
                setAlerta={setAlerta}
                setStatus={setStatus}
            />
        </div>
    )
}
