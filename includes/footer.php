    <!-- Bootstrap JS — necesario para componentes interactivos -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ================================================
         FOOTER — pie de página global del sistema
         Se incluye en todas las vistas mediante:
         require_once __DIR__ . '/../includes/footer.php'
         Contiene 3 columnas: logo, equipo y contacto
    ================================================ -->
    <footer class="mt-auto" style="background: #1a202c; color: #a0aec0;">

        <!-- Franja decorativa superior con gradiente azul -->
        <div style="height: 3px; background: linear-gradient(90deg, #63b3ed, #4facfe, #00f2fe);"></div>

        <!-- Contenedor principal del footer con padding vertical -->
        <div class="container py-5">

            <!-- justify-content-center centra las columnas horizontalmente -->
            <div class="row gy-4 justify-content-center">

                <!-- ── COLUMNA 1: Logo y descripción del proyecto ── -->
                <div class="col-md-3">

                    <!-- Logo: icono de reloj + nombre de la app -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-clock-history fs-5" style="color:#63b3ed;"></i>
                        <span class="fw-bold text-white" style="font-size:1.1rem;">AsistenciaApp</span>
                    </div>

                    <!-- Descripción corta del sistema y contexto académico -->
                    <p style="font-size:0.85rem; line-height:1.8;">
                        Sistema web de control de asistencia para empleados. Desarrollado como proyecto académico en Ibagué, Tolima — 2026.
                    </p>

                </div>

                <!-- ── COLUMNA 2: Integrantes del equipo ── -->
                <div class="col-md-3">

                    <h6 class="text-uppercase fw-bold mb-4" style="color:#63b3ed; letter-spacing:1px;">Equipo</h6>

                    <!-- Lista de integrantes con nombre y rol -->
                    <ul class="list-unstyled" style="font-size:0.87rem; line-height:2.2;">

                        <!-- Integrante 1 -->
                        <li>
                            <span class="text-white">Paula Ruiz</span>
                            <span style="font-size:0.78rem;"> — Líder de proyecto</span>
                        </li>

                        <!-- Integrante 2 -->
                        <li>
                            <span class="text-white">Juana Ramírez</span>
                            <span style="font-size:0.78rem;"> — Módulo administrador</span>
                        </li>

                        <!-- Integrante 3 -->
                        <li>
                            <span class="text-white">Kevin</span>
                            <span style="font-size:0.78rem;"> — Módulo empleados</span>
                        </li>

                    </ul>

                </div>

                <!-- ── COLUMNA 3: Información de contacto ── -->
                <div class="col-md-3">

                    <h6 class="text-uppercase fw-bold mb-4" style="color:#63b3ed; letter-spacing:1px;">Contacto</h6>

                    <ul class="list-unstyled" style="font-size:0.87rem; line-height:2.2;">

                        <!-- Correo electrónico del proyecto -->
                        <!-- mailto: abre el cliente de correo del usuario al hacer clic -->
                        <li>
                            <i class="bi bi-envelope me-2" style="color:#63b3ed;"></i>
                            <a href="mailto:sistema_asistencia01@gmail.com" style="color:#a0aec0; text-decoration:none;">
                                sistema_asistencia01@gmail.com
                            </a>
                        </li>

                        <!-- Ubicación del proyecto -->
                        <li>
                            <i class="bi bi-geo-alt me-2" style="color:#63b3ed;"></i>
                            Ibagué, Tolima — Colombia
                        </li>

                    </ul>

                </div>

            </div><!-- fin .row -->

        </div><!-- fin .container -->

        <!-- ── Franja inferior con derechos reservados ── -->
        <div style="border-top: 1px solid #2d3748;">
            <div class="container py-3 text-center">
                <!-- date('Y') imprime el año actual dinámicamente desde PHP -->
                <span style="font-size:0.78rem; color:#4a5568;">
                    &copy; <?= date('Y') ?> AsistenciaApp — Todos los derechos reservados.
                </span>
            </div>
        </div>

    </footer>

</body>   <!-- Cierra el body abierto en header.php -->
</html>  <!-- Cierra el html abierto en header.php -->