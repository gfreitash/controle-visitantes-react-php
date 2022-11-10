import React, {useState} from "react";
import {Nav, Navbar, NavDropdown} from "react-bootstrap";

import logo from "../assets/imgs/logo-light.png";
import {LinkContainer} from "react-router-bootstrap";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import ModalRelatorioVisitas, {emitirRelatorioVisita} from "./ModalRelatorioVisitas";
import ModalRelatorioVisitante, {emitirRelatorioVisitante} from "./ModalRelatorioVisitante";
import ModalCadastroUsuario from "./ModalCadastroUsuario";
import useAuth from "../hooks/useAuth";

export default function Header(props) {
    const LOGOUT_URL = "/logout"

    const {auth} = useAuth();
    const axiosPrivate = useAxiosPrivate();
    const handleInvalidSession = useInvalidSessionHandler();

    const [exibirModalRelVisita, setExibirModalRelVisita] = useState(false);
    const [exibirModalRelVisitante, setExibirModalRelVisitante] = useState(false);
    const [exibirModalCadastroUsuario, setExibirModalCadastroUsuario] = useState(false);

    const handleLogout = async () => {
        try {
            const response = await axiosPrivate.post(LOGOUT_URL);
            if(response.status === 200) {
                const alerta = {mensagem: "Você efetuou logout", tipo: "primary"};
                handleInvalidSession(alerta, false);
            }
        } catch (err) {
            handleInvalidSession();
        }
    }

    const handleEmitirRelDia = async (funcaoEmissao) => {
        const data = (new Date()).toISOString().split('T')[0];

        const args = {
            axios: axiosPrivate,
            dataInicio: data,
        }
        try {
            await funcaoEmissao(args);
        } catch (error) {
            if (error.response.status === 401) {
                handleInvalidSession();
            }
        }
    }

    return (
        <header className="sticky-top">
            <Navbar variant="dark" className="pge-bg-gray">
                <div className="d-inline-flex p-2">
                    <LinkContainer to="/inicio">
                        <Navbar.Brand>
                            <img className="header-logo" src={logo} alt="Logo"/>
                        </Navbar.Brand>
                    </LinkContainer>

                    <Navbar.Collapse className="collapse navbar-collapse" id="navbarNav">
                        <Nav>
                            <LinkContainer to="/inicio">
                                <Nav.Link>Início</Nav.Link>
                            </LinkContainer>

                            <NavDropdown title="Visitantes" id="basic-nav-dropdown">
                                <LinkContainer to="/novo-cadastro">
                                    <NavDropdown.Item>Cadastrar</NavDropdown.Item>
                                </LinkContainer>
                                <LinkContainer to="/lista-visitantes">
                                    <NavDropdown.Item>Lista de Visitantes</NavDropdown.Item>
                                </LinkContainer>
                            </NavDropdown>

                            <NavDropdown title="Entrada e Saída" id="basic-nav-dropdown">
                                <LinkContainer to="/nova-visita">
                                    <NavDropdown.Item>Cadastrar nova visita</NavDropdown.Item>
                                </LinkContainer>
                                <LinkContainer to="/lista-visitas/todas">
                                    <NavDropdown.Item>Lista de todas as visitas</NavDropdown.Item>
                                </LinkContainer>
                                <NavDropdown.Divider/>
                                <LinkContainer to="/lista-visitas/abertas">
                                    <NavDropdown.Item>Lista de visitas abertas</NavDropdown.Item>
                                </LinkContainer>
                                <LinkContainer to="/lista-visitas/fechadas">
                                    <NavDropdown.Item>Lista de visitas finalizadas</NavDropdown.Item>
                                </LinkContainer>
                            </NavDropdown>

                            <NavDropdown title="Relatórios" id="basic-nav-dropdown">
                                <NavDropdown.Item onClick={()=>setExibirModalRelVisitante(true)}>
                                    Relatório de visitantes
                                </NavDropdown.Item>
                                <NavDropdown.Item onClick={()=>handleEmitirRelDia(emitirRelatorioVisitante)}>
                                    Relatório de visitantes do dia
                                </NavDropdown.Item>

                                <NavDropdown.Divider/>

                                <NavDropdown.Item onClick={()=>setExibirModalRelVisita(true)}>
                                    Relatório de visitas
                                </NavDropdown.Item>
                                <NavDropdown.Item onClick={()=>handleEmitirRelDia(emitirRelatorioVisita)}>
                                    Relatório de visitas do dia
                                </NavDropdown.Item>
                            </NavDropdown>
                        </Nav>
                    </Navbar.Collapse>
                </div>

                <div className="collapse navbar-collapse d-flex flex-row-reverse container-flex" id="navbarNav">
                    <Nav>
                        <NavDropdown title={props.usuario} align="end">
                            {auth.funcao === 1 &&
                                <>
                                    <NavDropdown.Item onClick={()=>setExibirModalCadastroUsuario(true)}>
                                        Cadastrar Usuário
                                    </NavDropdown.Item>
                                    <NavDropdown.Divider/>
                                </>
                            }

                            <LinkContainer to="/alterar-senha">
                                <NavDropdown.Item>Alterar Senha</NavDropdown.Item>
                            </LinkContainer>

                            <NavDropdown.Item onClick={handleLogout}>Sair</NavDropdown.Item>
                        </NavDropdown>
                    </Nav>
                </div>
            </Navbar>
            {exibirModalRelVisita &&
                <ModalRelatorioVisitas
                    exibir={exibirModalRelVisita}
                    onFechar={setExibirModalRelVisita}
                />
            }
            {exibirModalRelVisitante &&
                <ModalRelatorioVisitante
                    exibir={exibirModalRelVisitante}
                    onFechar={setExibirModalRelVisitante}
                />
            }
            {exibirModalCadastroUsuario &&
                <ModalCadastroUsuario
                    exibir={exibirModalCadastroUsuario}
                    onFechar={setExibirModalCadastroUsuario}
                />}
        </header>
    )
}
