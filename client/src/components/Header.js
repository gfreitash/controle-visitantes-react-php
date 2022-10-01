import React from "react";
import {Nav, Navbar, NavDropdown} from "react-bootstrap";

import logo from "../assets/imgs/logo-light.png";
import {LinkContainer} from "react-router-bootstrap";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import useAxiosPrivate from "../hooks/useAxiosPrivate";

export default function Header(props) {
    const LOGOUT_URL = "/logout"
    const axiosPrivate = useAxiosPrivate();
    const handleInvalidSession = useInvalidSessionHandler();

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
                                <NavDropdown.Divider/>
                                <LinkContainer to="/visitas-abertas">
                                    <NavDropdown.Item>Visitas em aberto</NavDropdown.Item>
                                </LinkContainer>
                                <LinkContainer to="/visitas-finalizadas">
                                    <NavDropdown.Item>Visitas finalizadas</NavDropdown.Item>
                                </LinkContainer>
                            </NavDropdown>

                            <NavDropdown title="Relatórios" id="basic-nav-dropdown">
                                <LinkContainer to="/relatorio-visitantes">
                                    <NavDropdown.Item>Relatório de visitantes</NavDropdown.Item>
                                </LinkContainer>
                                <LinkContainer to="/relatorio-visitantes">
                                    <NavDropdown.Item>Relatório de visitantes do dia</NavDropdown.Item>
                                </LinkContainer>
                                <NavDropdown.Divider/>
                                <LinkContainer to="/relatorio-visitas">
                                    <NavDropdown.Item>Relatório de visitas</NavDropdown.Item>
                                </LinkContainer>
                                <LinkContainer to="/relatorio-visitas">
                                    <NavDropdown.Item>Relatório de visitas do dia</NavDropdown.Item>
                                </LinkContainer>
                            </NavDropdown>
                        </Nav>
                    </Navbar.Collapse>
                </div>

                <div className="collapse navbar-collapse d-flex flex-row-reverse container-flex" id="navbarNav">
                    <Nav>
                        <NavDropdown title={props.usuario} align="end">
                            <LinkContainer to="/alterar-senha">
                                <NavDropdown.Item>Alterar Senha</NavDropdown.Item>
                            </LinkContainer>

                            <NavDropdown.Item onClick={handleLogout}>Sair</NavDropdown.Item>
                        </NavDropdown>
                    </Nav>
                </div>
            </Navbar>
        </header>
    )
}
