<!-- start: sidebar -->
<aside id="sidebar-left" class="sidebar-left">

    <div class="sidebar-header">
        <div class="sidebar-title text-primary">
            Panel de Administración
        </div>
        <div class="sidebar-toggle d-none d-md-block" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
            <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
        </div>
    </div>

    <div class="nano">
        <div class="nano-content">
            <nav id="menu" class="nav-main" role="navigation">

                <ul class="nav nav-main">

                    <!-- Dashboard -->
                    <?php if (
                        !empty($data['permisos'][MOD_DASHBOARD_RH]['r']) ||
                        !empty($data['permisos'][MOD_DASHBOARD_VENTAS]['r'])
                    ) {
                    ?>
                        <li class="nav-parent <?php if (
                                                    $data['menu'] == MOD_DASHBOARD_RH ||
                                                    $data['menu'] == MOD_DASHBOARD_VENTAS
                                                ) {
                                                    echo "nav-expanded nav-active";
                                                } ?>">
                            <a class="nav-link" href="#">
                                <i class="fa-sharp fa-light fa-gauge"></i>
                                <span>Dashboard</span>
                            </a>
                            <ul class="nav nav-children ">

                                <?php if (
                                    !empty($data['permisos'][MOD_DASHBOARD_RH]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_DASHBOARD_RH) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/dashboard/recursoshumanos">
                                            Recursos Humanos
                                        </a>
                                    </li>
                                <?php }  ?>

                                <?php if (
                                    !empty($data['permisos'][MOD_DASHBOARD_VENTAS]['r'])
                                ) { ?>

                                    <?php if (!empty($data['permisos'][MOD_DASHBOARD_RH]['r'])) { ?>
                                        <hr class="mb-1 mt-1">
                                    <?php } ?>

                                    <li class="<?= ($data['menu'] == MOD_DASHBOARD_VENTAS) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/dashboard/ventas">
                                            Ventas
                                        </a>
                                    </li>
                                <?php }  ?>

                            </ul>
                        </li>
                    <?php }  ?>


                    <!-- Configuración -->
                    <?php if (
                        !empty($data['permisos'][MOD_CONFIGURACION]['r']) ||
                        !empty($data['permisos'][MOD_EFIRMA]['r'])
                    ) {
                    ?>
                        <li class="nav-parent <?php if (
                                                    $data['menu'] == MOD_CONFIGURACION ||
                                                    $data['menu'] == MOD_EFIRMA
                                                ) {
                                                    echo "nav-expanded nav-active";
                                                } ?>">
                            <a class="nav-link" href="#">
                                <i class="fa-sharp fa-light fa-gear"></i>
                                <span>Configuración</span>
                            </a>
                            <ul class="nav nav-children ">

                                <?php if (
                                    !empty($data['permisos'][MOD_CONFIGURACION]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_CONFIGURACION) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/configuracion">
                                            General
                                        </a>
                                    </li>
                                <?php }  ?>

                                <?php if (
                                    !empty($data['permisos'][MOD_EFIRMA]['r'])
                                ) { ?>

                                    <hr class="mb-1 mt-1">

                                    <li class="<?= ($data['menu'] == MOD_EFIRMA) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/eFirma">
                                            e-Firma
                                        </a>
                                    </li>
                                <?php }  ?>

                            </ul>
                        </li>
                    <?php }  ?>


                    <!-- Seguridad -->
                    <?php if (
                        !empty($data['permisos'][MOD_USUARIOS]['r']) ||
                        !empty($data['permisos'][MOD_ROLES]['r'])
                    ) {
                    ?>
                        <li class="nav-parent <?php if (
                                                    $data['menu'] == MOD_USUARIOS ||
                                                    $data['menu'] == MOD_ROLES
                                                ) {
                                                    echo "nav-expanded nav-active";
                                                } ?>">
                            <a class="nav-link" href="#">
                                <i class="fa-sharp fa-light fa-lock-keyhole"></i>
                                <span>Seguridad</span>
                            </a>
                            <ul class="nav nav-children ">

                                <?php if (
                                    !empty($data['permisos'][MOD_USUARIOS]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_USUARIOS) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/usuarios">
                                            Usuarios
                                        </a>
                                    </li>
                                <?php }  ?>

                                <?php if (
                                    !empty($data['permisos'][MOD_ROLES]['r'])
                                ) { ?>

                                    <hr class="mb-1 mt-1">

                                    <li class="<?= ($data['menu'] == MOD_ROLES) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/roles">
                                            Roles
                                        </a>
                                    </li>
                                <?php }  ?>

                            </ul>
                        </li>
                    <?php }  ?>

                    <!-- Sitio Web  -->
                    <?php if (
                        !empty($data['permisos'][MOD_CONTACTANOS]['r']) ||
                        !empty($data['permisos'][MOD_LEGALES]['r']) ||
                        !empty($data['permisos'][MOD_CAT_PREGUNTAS_FRECUENTES]['r'])
                    ) {
                    ?>

                        <!-- <i class="fa-duotone fa-light fa-globe"></i> -->
                        <li class="nav-parent <?php if (
                                                    $data['menu'] == MOD_CONTACTANOS ||
                                                    $data['menu'] == MOD_LEGALES ||
                                                    $data['menu'] == MOD_CAT_PREGUNTAS_FRECUENTES
                                                ) {
                                                    echo "nav-expanded nav-active";
                                                } ?>">
                            <a class="nav-link" href="#">
                                <i class="fa-sharp fa-light fa-globe"></i>
                                <span>Sitio Web</span>
                            </a>
                            <ul class="nav nav-children ">

                                <?php if (
                                    !empty($data['permisos'][MOD_LEGALES]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_LEGALES) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/legales">
                                            Legales
                                        </a>
                                    </li>
                                <?php }  ?>



                                <?php if (
                                    !empty($data['permisos'][MOD_CAT_PREGUNTAS_FRECUENTES]['r'])
                                ) { ?>

                                    <hr class="mb-1 mt-1">

                                    <li class="<?= ($data['menu'] == MOD_CAT_PREGUNTAS_FRECUENTES) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/preguntasFrecuentes">
                                            FAQ
                                        </a>
                                    </li>
                                <?php }  ?>



                                <?php if (
                                    !empty($data['permisos'][MOD_CONTACTANOS]['r'])
                                ) { ?>

                                    <hr class="mb-1 mt-1">

                                    <li class="<?= ($data['menu'] == MOD_CONTACTANOS) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/contactanos">
                                            Mensajes de Contacto
                                        </a>
                                    </li>

                                <?php }  ?>


                            </ul>
                        </li>
                    <?php }  ?>

                    <!-- Administración  -->
                    <?php if (
                        !empty($data['permisos'][MOD_RECURSOS_HUMANOS]['r'])
                    ) {
                    ?>

                        <!-- <i class="fa-duotone fa-light fa-globe"> <i class="fa-brands fa-squarespace<i class="fa-duotone fa-thin fa-user-gear"></i>"></i></i> -->
                        <li class="nav-parent <?php if (
                                                    $data['menu'] == MOD_RECURSOS_HUMANOS
                                                ) {
                                                    echo "nav-expanded nav-active";
                                                } ?>">
                            <a class="nav-link" href="#">
                                <i class="fa-sharp fa-light fa-user-gear"></i>
                                <span>Administración</span>
                            </a>
                            <ul class="nav nav-children ">

                                <?php if (
                                    !empty($data['permisos'][MOD_RECURSOS_HUMANOS]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_RECURSOS_HUMANOS) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/personal">
                                            Personal
                                        </a>
                                    </li>
                                <?php }  ?>

                                <hr class="mb-1 mt-1">

                            </ul>
                        </li>
                    <?php }  ?>

                    <!-- Ventas  -->
                    <?php if (
                        !empty($data['permisos'][MOD_OPORTUNIDAD_VENTA]['r'])
                    ) {
                    ?>

                        <!-- <i class="fa-duotone fa-light fa-globe"></i> -->
                        <li class="nav-parent <?php if (
                                                    $data['menu'] == MOD_OPORTUNIDAD_VENTA
                                                ) {
                                                    echo "nav-expanded nav-active";
                                                } ?>">
                            <a class="nav-link" href="#">
                                <img class="img_icons_aside" src="<?= assets(); ?>/img/icons/ventas_web_bco.png" alt="">
                                <!-- <i class="fa-sharp fa-light fa-globe"></i> -->
                                <span>Ventas</span>
                            </a>
                            <ul class="nav nav-children ">

                                <?php if (
                                    !empty($data['permisos'][MOD_OPORTUNIDAD_VENTA]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == 1000) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/legales">
                                            Oportunidad de Venta
                                        </a>
                                    </li>
                                <?php }  ?>

                                <hr class="mb-1 mt-1">

                            </ul>
                        </li>
                    <?php }  ?>

                    <!-- Seguimiento  -->
                    <?php if (
                        !empty($data['permisos'][MOD_SEGUIMIENTO_ORDENES_CLIENTE]['r']) ||
                        !empty($data['permisos'][MOD_SEGUIMIENTO_PROYECTO_VENTA]['r']) ||
                        !empty($data['permisos'][MOD_SEGUIMIENTO_ORDENES_PROVEEDOR]['r'])
                    ) {
                    ?>

                        <li class="nav-parent <?php if (
                                                    $data['menu'] == MOD_SEGUIMIENTO_ORDENES_CLIENTE ||
                                                    $data['menu'] == MOD_SEGUIMIENTO_PROYECTO_VENTA ||
                                                    $data['menu'] == MOD_SEGUIMIENTO_ORDENES_PROVEEDOR
                                                ) {
                                                    echo "nav-expanded nav-active";
                                                } ?>">
                            <a class="nav-link" href="#">
                                <i class="fa-sharp fa-light fa-cart-shopping-fast"></i>
                                <span>Seguimiento</span>
                            </a>
                            <ul class="nav nav-children ">

                                <?php if (
                                    !empty($data['permisos'][MOD_SEGUIMIENTO_PROYECTO_VENTA]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_SEGUIMIENTO_PROYECTO_VENTA) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/seguimiento/proyectoVenta">
                                            Proyecto de Venta
                                        </a>
                                    </li>
                                <?php }  ?>
                             
                                 <hr class="mb-1 mt-1">

                                <?php if (
                                    !empty($data['permisos'][MOD_SEGUIMIENTO_ORDENES_CLIENTE]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_SEGUIMIENTO_ORDENES_CLIENTE) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/seguimiento/ordenesCliente">
                                            Órdenes de Compra Clientes
                                        </a>
                                    </li>
                                <?php }  ?>

                                <hr class="mb-1 mt-1">

                                <?php if (
                                    !empty($data['permisos'][MOD_SEGUIMIENTO_ORDENES_PROVEEDOR]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_SEGUIMIENTO_ORDENES_PROVEEDOR) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/seguimiento/ordenesProveedor">
                                            Órdenes de Compra Proveedores
                                        </a>
                                    </li>
                                <?php }  ?>

                                <hr class="mb-1 mt-1">

                            </ul>
                        </li>
                    <?php }  ?>

                    <!-- Almacén  -->
                    <?php if (
                        !empty($data['permisos'][MOD_ALMACEN_PRODUCTOS]['r']) ||
                        !empty($data['permisos'][MOD_ALMACEN_INVENTARIO]['r'])
                    ) {
                    ?>

                        <li class="nav-parent <?php if (
                                                    $data['menu'] == MOD_ALMACEN_PRODUCTOS ||
                                                    $data['menu'] == MOD_ALMACEN_INVENTARIO
                                                ) {
                                                    echo "nav-expanded nav-active";
                                                } ?>">
                            <a class="nav-link" href="#">
                                <i class="fa-sharp fa-light fa-boxes-stacked"></i>
                                <span>Almacén</span>
                            </a>
                            <ul class="nav nav-children ">

                                <?php if (
                                    !empty($data['permisos'][MOD_ALMACEN_PRODUCTOS]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_ALMACEN_PRODUCTOS) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/almacen/productos">
                                            Productos
                                        </a>
                                    </li>
                                <?php }  ?>

                                <hr class="mb-1 mt-1">

                                <?php if (
                                    !empty($data['permisos'][MOD_ALMACEN_INVENTARIO]['r'])
                                ) { ?>
                                    <li class="<?= ($data['menu'] == MOD_ALMACEN_INVENTARIO) ? "nav-active" : ""; ?>">
                                        <a class="nav-link" href="<?= base_url(); ?>/almacen/inventario">
                                            Inventario
                                        </a>
                                    </li>
                                <?php }  ?>

                                <hr class="mb-1 mt-1">

                            </ul>
                        </li>
                    <?php }  ?>

                </ul>

            </nav>

            <hr class="separator text-primary" />

            <div class="sidebar-widget widget-tasks">
                <p>Version <?= VERSION_SYS ?></p>
            </div>

            <div class="d-none sidebar-widget widget-tasks">
                <div class="widget-header">
                    <h6>Enlaces</h6>
                    <div class="widget-toggle">+</div>
                </div>
                <div class="widget-content">
                    <ul class="list-unstyled m-0">
                        <li><a target="_blank" href="<?= $data['configuracion']['url_tienda']  ?>">Ir a Tienda</a></li>
                    </ul>
                </div>
            </div>

        </div>

        <script>
            // Maintain Scroll Position
            if (typeof localStorage !== 'undefined') {
                if (localStorage.getItem('sidebar-left-position') !== null) {
                    var initialPosition = localStorage.getItem('sidebar-left-position'),
                        sidebarLeft = document.querySelector('#sidebar-left .nano-content');

                    sidebarLeft.scrollTop = initialPosition;
                }
            }
        </script>

    </div>

</aside>
<!-- end: sidebar -->