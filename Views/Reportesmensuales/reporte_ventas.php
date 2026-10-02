<?php require_once('Template/header_01.php'); ?>
<?php require_once('Template/header_02.php'); ?>

<section role="main" class="content-body fondo-general">
    <header class="page-header">
        <h2><?= $data['page_form_title']; ?></h2>
        <div class="right-wrapper text-end">
            <ol class="breadcrumbs">
                <li>
                    <a href="<?= base_url(); ?>/inicio" aria-label="Inicio">
                        <i class="bx bx-home-alt" aria-hidden="true"></i>
                    </a>
                </li>
                <li><span><?= htmlspecialchars($data['page_breadcrumb'], ENT_QUOTES, 'UTF-8'); ?></span></li>
            </ol>
            <div class="sidebar-right-toggle" style="cursor: default;"></div>
        </div>
    </header>

    <!-- Área de contenido en blanco para el reporte mensual de ventas. -->
</section>

<?php require_once('Template/footer_01.php'); ?>
<?php require_once('Template/footer_02.php'); ?>
