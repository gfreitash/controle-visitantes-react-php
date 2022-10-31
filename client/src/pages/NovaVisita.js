import React, {useRef, useState} from "react";
import {Link, useNavigate} from "react-router-dom";

import useAxiosPrivate from "../hooks/useAxiosPrivate";
import useQuery from "../hooks/useQuery";
import useAuth from "../hooks/useAuth";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";

import {validaCPF} from "../assets/js/dados-visitante";

import Titulo from "../components/Titulo";
import FotoVisitante from "../components/FotoVisitante";
import DadosVisitante from "../components/DadosVisitante";
import DadosVisita from "../components/DadosVisita";
import Alerta from "../components/Alerta";

export default function NovaVisita() {
    const axios = useAxiosPrivate();
    const navigate = useNavigate();
    const query = useQuery();
    const {auth} = useAuth();
    const handleInvalidSession = useInvalidSessionHandler();

    const alertaRef = useRef();

    const cpf = query.get("cpf") ?? "";
    const [visitante, setVisitante] = useState({});
    const [buscaRealizada, setBuscaRealizada] = useState(false);
    const [alerta, setAlerta] = useState({tipo: "", mensagem: ""});

    const conteudoTitulo = (
        <Link to={`/visitante?cpf=${visitante.cpf}`}>
            <button className="btn btn-dark btn-acao" type="button"
                    style={visitante.cpf ? {} : {display: "none"}}>
                Editar visitante
            </button>
        </Link>
    );

    const onCpfValido = (codigoResposta, visitante) => {
        setVisitante(visitante);
        setBuscaRealizada(true);
        setAlerta({tipo:"", mensagem: ""});
    }

    async function handleSubmit(event) {
        event.preventDefault();
        if(!validaCPF(event.target.cpf, ()=>{})) {
            return;
        }

        try {
            const formData = new FormData(event.target);
            formData.append("idUsuario", auth.id);
            const response = await axios.post("/visita", formData);

            if (response.status === 201 || response.status === 200) {
                const idVisita = response.data.id;
                navigate(`/visita?id=${idVisita}`);
            }
        } catch (error) {
            switch (error.response.status) {
                case 400:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível cadastrar a visita. Verifique os dados e tente novamente."});
                    break;
                case 401:
                    handleInvalidSession();
                    break;
                case 500:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível cadastrar a visita. Houve um erro desconhecido no servidor."});
                    break;
                case 0:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível cadastrar a visita. O servidor está inacessível."});
                    break;
                default:
                    setAlerta({tipo: "danger", mensagem: "Não foi possível cadastrar a visita. Houve um erro desconhecido."});
            }
        }
    }

    return (
        <div className="form-fieldset">
            <Titulo conteudoTitulo={conteudoTitulo}>Nova Visita</Titulo>
            <hr/>
            <Alerta alerta={alerta} setAlerta={setAlerta} alertaRef={alertaRef}/>
            <form id='form' encType='multipart/form-data' onSubmit={handleSubmit}>
                <section className="form">
                    <FotoVisitante foto={visitante.id ? visitante.foto : ""} disabled/>

                    <div className="form-wrapper">
                        <div className="width--95">
                            <section>
                                <DadosVisitante estado="disabled" cpfAutoFocus cpf={cpf}
                                                onCpfValido={onCpfValido} buscarDados/>
                                <hr/>
                                <DadosVisita disabled={!buscaRealizada || !visitante}/>
                            </section>
                        </div>
                    </div>
                </section>

                <div className="acao mt-3" id="acao">
                    <Link to="/inicio">
                        <button type="button" className="btn btn-secondary btn-acao">Cancelar</button>
                    </Link>

                    <button type="submit" className="btn btn-primary btn-acao"
                            disabled={!buscaRealizada || !visitante}>
                        Cadastrar visita
                    </button>
                </div>
            </form>
        </div>
    )
}
