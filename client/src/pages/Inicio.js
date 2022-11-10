import React, {useEffect, useState} from "react";
import useAuth from "../hooks/useAuth";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import {toTitleCase} from "../assets/js/utils";

import "../assets/css/inicio.css"
import Titulo from "../components/Titulo";
import Caixa from "../components/Caixa";
import {Card, Nav} from "react-bootstrap";
import {Link} from "react-router-dom";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faSquarePlus} from "@fortawesome/free-solid-svg-icons";
import Alerta from "../components/Alerta";

export default function Inicio() {
    const {auth} = useAuth();
    const axios = useAxiosPrivate();

    const [alerta, setAlerta] = useState({tipo: "", mensagem: ""});
    const [abaVisitanteAtiva, setAbaVisitanteAtiva] = useState("ativos");
    const [dados, setDados] = useState(null);

    const larguraCaixaVisitante = "24.5%";
    const larguraCaixaVisita = "19.5%";
    const linkVisitasAberto = "/lista-visitas/abertas";
    const linkVisitas = "/lista-visitas/todas?dataInicio=";
    const linkVisitantesAtivos = "/lista-visitantes?status=ativos&dataInicio=";
    const linkVisitantesCadastrados = "/lista-visitantes?status=cadastrados&dataInicio=";

    const usuario = auth?.nome ? toTitleCase(auth.nome).split(' ')[0] : "";

    useEffect(() => {
        const controlador = new AbortController();
        let isMounted = true;

        if (auth?.alerta) {
            setAlerta(auth.alerta);
        }

        const obterDados = async () => {
            try {
                const resposta = await axios.get("/dashboard", {signal: controlador.signal});
                if(resposta?.status === 200) {
                    setDados(resposta.data);
                }
            } catch (e) {
                if (e.code !== "ERR_CANCELED") {
                    console.log(e);
                }
            }
        }

        isMounted && obterDados();

        return () => {
            isMounted = false;
            controlador.abort();
        }

    },[]);

    return (
        <>
            <Titulo titulo="Início">Boas vindas, {usuario}!</Titulo>
            <hr/>
            <Alerta alerta={alerta} setAlerta={setAlerta}/>
            <section className="inicio__display width--100">
                <div className="flex-grow-1">
                    <Card>
                        <Card.Header className="d-flex justify-content-between">
                            <Nav variant="tabs" activeKey="visitas">
                                <Nav.Item>
                                    <Nav.Link eventKey="visitas">
                                        <h4>Visitas</h4>
                                    </Nav.Link>
                                </Nav.Item>
                            </Nav>

                            <div className="align-self-center inicio__icone d-flex align-items-center justify-content-center">
                                <Link to="/nova-visita">
                                    <FontAwesomeIcon icon={faSquarePlus} size="xl"/>
                                </Link>
                            </div>
                        </Card.Header>

                        <Card.Body>
                            <div className= "d-flex justify-content-between">
                                <Caixa link={linkVisitasAberto} conteudo="Em aberto" contador={dados?.visitas.abertas} razao="2:1" width={larguraCaixaVisita}/>
                                <Caixa link={linkVisitas+dados?.hoje} conteudo="Nesse dia" contador={dados?.visitas.hoje} razao="2:1" width={larguraCaixaVisita} color="#0d442a"/>
                                <Caixa link={linkVisitas+dados?.semana} conteudo="Nessa semana" contador={dados?.visitas.semana} razao="2:1" width={larguraCaixaVisita} color="#0d442a"/>
                                <Caixa link={linkVisitas+dados?.mes} conteudo="Nesse mês" contador={dados?.visitas.mes} razao="2:1" width={larguraCaixaVisita} color="#0d442a"/>
                                <Caixa link={linkVisitas+dados?.ano} conteudo="Nesse ano" contador={dados?.visitas.ano} razao="2:1" width={larguraCaixaVisita} color="#0d442a"/>
                            </div>
                        </Card.Body>
                    </Card>
                </div>
            </section>

            <section className="inicio__display width--100" >
                <Card className="flex-grow-1">
                    <Card.Header className="d-flex justify-content-between">
                        <Nav variant="tabs" activeKey={abaVisitanteAtiva}>
                            <Nav.Item>
                                <Nav.Link eventKey="ativos" onClick={()=>setAbaVisitanteAtiva("ativos")}>
                                    <h4>Visitantes Ativos</h4>
                                </Nav.Link>
                            </Nav.Item>
                            <Nav.Item>
                                <Nav.Link eventKey="cadastrados" onClick={()=>setAbaVisitanteAtiva("cadastrados")}>
                                    <h4>Visitantes Cadastrados</h4>
                                </Nav.Link>
                            </Nav.Item>
                        </Nav>

                        <div className="align-self-center inicio__icone d-flex align-items-center justify-content-center">
                            <Link to="/novo-cadastro">
                                <FontAwesomeIcon icon={faSquarePlus} size="xl"/>
                            </Link>
                        </div>
                    </Card.Header>

                    <Card.Body>
                        {abaVisitanteAtiva === "ativos" &&
                            (
                                <>
                                    <div className= "d-flex justify-content-between">
                                        <Caixa link={linkVisitantesAtivos+dados?.hoje} conteudo="Nesse dia" contador={dados?.visitantes.ativos.hoje} razao="2:1" width={larguraCaixaVisitante} color="#043d48"/>
                                        <Caixa link={linkVisitantesAtivos+dados?.semana} conteudo="Nessa semana" contador={dados?.visitantes.ativos.semana} razao="2:1" width={larguraCaixaVisitante} color="#043d48"/>
                                        <Caixa link={linkVisitantesAtivos+dados?.mes} conteudo="Nesse mês" contador={dados?.visitantes.ativos.mes} razao="2:1" width={larguraCaixaVisitante} color="#043d48"/>
                                        <Caixa link={linkVisitantesAtivos+dados?.ano} conteudo="Nesse ano" contador={dados?.visitantes.ativos.ano} razao="2:1" width={larguraCaixaVisitante} color="#043d48"/>
                                    </div>
                                </>
                            )
                        }
                        {abaVisitanteAtiva === "cadastrados" &&
                            <>
                                <div className= "d-flex justify-content-between">
                                    <Caixa link={linkVisitantesCadastrados+dados?.hoje} conteudo="Nesse dia" contador={dados?.visitantes.cadastrados.hoje} razao="2:1" width={larguraCaixaVisitante} color="#0D2744"/>
                                    <Caixa link={linkVisitantesCadastrados+dados?.semana} conteudo="Nessa semana" contador={dados?.visitantes.cadastrados.semana} razao="2:1" width={larguraCaixaVisitante} color="#0D2744"/>
                                    <Caixa link={linkVisitantesCadastrados+dados?.mes} conteudo="Nesse mês" contador={dados?.visitantes.cadastrados.mes} razao="2:1" width={larguraCaixaVisitante} color="#0D2744"/>
                                    <Caixa link={linkVisitantesCadastrados+dados?.ano} conteudo="Nesse ano" contador={dados?.visitantes.cadastrados.ano} razao="2:1" width={larguraCaixaVisitante} color="#0D2744"/>
                                </div>
                            </>

                        }
                    </Card.Body>
                </Card>
            </section>
        </>
    )
}
