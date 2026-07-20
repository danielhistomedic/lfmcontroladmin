       <!-- start: header -->
       <header class="header">
           <div class="logo-container">

               <a href="<?= base_url(); ?>/inicio" class="logo">
                   <img src="<?= assets(); ?>/img/logo.png" width="145" height="auto" alt="Logo" />
               </a>

               <div class="d-md-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
                   <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
               </div>

           </div>

           <!-- start: search & user box -->
           <div class=" header-right">

               <form action="pages-search-results.html" class="d-none search nav-form">
                   <div class="input-group">
                       <input type="text" class="form-control" name="q" id="q" placeholder="Buscar...">
                       <button class="btn btn-default" type="submit"><i class="bx bx-search"></i></button>
                   </div>
               </form>

               <span class="d-none separator"></span>

               <ul class="d-none notifications">
                   <li>
                       <a href="#" class="dropdown-toggle notification-icon" data-bs-toggle="dropdown">
                           <i class="bx bx-list-ol"></i>
                           <span class="badge">3</span>
                       </a>

                       <div class="dropdown-menu notification-menu large">
                           <div class="notification-title">
                               <span class="float-end badge badge-default">3</span> Tasks
                           </div>

                           <div class="content">
                               <ul>
                                   <li>
                                       <p class="clearfix mb-1">
                                           <span class="message float-start">Generating Sales Report</span>
                                           <span class="message float-end text-dark">60%</span>
                                       </p>
                                       <div class="progress progress-xs light">
                                           <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;"></div>
                                       </div>
                                   </li>

                                   <li>
                                       <p class="clearfix mb-1">
                                           <span class="message float-start">Importing Contacts</span>
                                           <span class="message float-end text-dark">98%</span>
                                       </p>
                                       <div class="progress progress-xs light">
                                           <div class="progress-bar" role="progressbar" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100" style="width: 98%;"></div>
                                       </div>
                                   </li>

                                   <li>
                                       <p class="clearfix mb-1">
                                           <span class="message float-start">Uploading something big</span>
                                           <span class="message float-end text-dark">33%</span>
                                       </p>
                                       <div class="progress progress-xs light mb-1">
                                           <div class="progress-bar" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100" style="width: 33%;"></div>
                                       </div>
                                   </li>
                               </ul>
                           </div>
                       </div>
                   </li>
                   <li>
                       <a href="#" class="dropdown-toggle notification-icon" data-bs-toggle="dropdown">
                           <i class="bx bx-envelope"></i>
                           <span class="badge">4</span>
                       </a>

                       <div class="dropdown-menu notification-menu">
                           <div class="notification-title">
                               <span class="float-end badge badge-default">230</span> Messages
                           </div>

                           <div class="content">
                               <ul>
                                   <li>
                                       <a href="#" class="clearfix">
                                           <figure class="image">
                                               <img src="<?= assets(); ?>/img/!sample-user.jpg" alt="Joseph Doe Junior" class="rounded-circle" />
                                           </figure>
                                           <span class="title">Joseph Doe</span>
                                           <span class="message">Lorem ipsum dolor sit.</span>
                                       </a>
                                   </li>
                                   <li>
                                       <a href="#" class="clearfix">
                                           <figure class="image">
                                               <img src="<?= assets(); ?>/img/!sample-user.jpg" alt="Joseph Junior" class="rounded-circle" />
                                           </figure>
                                           <span class="title">Joseph Junior</span>
                                           <span class="message truncate">Truncated message. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sit amet lacinia orci. Proin vestibulum eget risus non luctus. Nunc cursus lacinia lacinia. Nulla molestie malesuada est ac tincidunt. Quisque eget convallis diam, nec venenatis risus. Vestibulum blandit faucibus est et malesuada. Sed interdum cursus dui nec venenatis. Pellentesque non nisi lobortis, rutrum eros ut, convallis nisi. Sed tellus turpis, dignissim sit amet tristique quis, pretium id est. Sed aliquam diam diam, sit amet faucibus tellus ultricies eu. Aliquam lacinia nibh a metus bibendum, eu commodo eros commodo. Sed commodo molestie elit, a molestie lacus porttitor id. Donec facilisis varius sapien, ac fringilla velit porttitor et. Nam tincidunt gravida dui, sed pharetra odio pharetra nec. Duis consectetur venenatis pharetra. Vestibulum egestas nisi quis elementum elementum.</span>
                                       </a>
                                   </li>
                                   <li>
                                       <a href="#" class="clearfix">
                                           <figure class="image">
                                               <img src="<?= assets(); ?>/img/!sample-user.jpg" alt="Joe Junior" class="rounded-circle" />
                                           </figure>
                                           <span class="title">Joe Junior</span>
                                           <span class="message">Lorem ipsum dolor sit.</span>
                                       </a>
                                   </li>
                                   <li>
                                       <a href="#" class="clearfix">
                                           <figure class="image">
                                               <img src="<?= assets(); ?>/img/!sample-user.jpg" alt="Joseph Junior" class="rounded-circle" />
                                           </figure>
                                           <span class="title">Joseph Junior</span>
                                           <span class="message">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sit amet lacinia orci. Proin vestibulum eget risus non luctus. Nunc cursus lacinia lacinia. Nulla molestie malesuada est ac tincidunt. Quisque eget convallis diam.</span>
                                       </a>
                                   </li>
                               </ul>

                               <hr />

                               <div class="text-end">
                                   <a href="#" class="view-more">View All</a>
                               </div>
                           </div>
                       </div>
                   </li>

                   <li>
                       <a href="#" class="dropdown-toggle notification-icon" data-bs-toggle="dropdown">
                           <i class="bx bx-bell"></i>
                           <span class="badge">3</span>
                       </a>

                       <div class="dropdown-menu notification-menu">
                           <div class="notification-title">
                               <span class="float-end badge badge-default">3</span> Alerts
                           </div>

                           <div class="content">
                               <ul>
                                   <li>
                                       <a href="#" class="clearfix">
                                           <div class="image">
                                               <i class="fas fa-thumbs-down bg-danger text-light"></i>
                                           </div>
                                           <span class="title">Server is Down!</span>
                                           <span class="message">Just now</span>
                                       </a>
                                   </li>
                                   <li>
                                       <a href="#" class="clearfix">
                                           <div class="image">
                                               <i class="bx bx-lock bg-warning text-light"></i>
                                           </div>
                                           <span class="title">User Locked</span>
                                           <span class="message">15 minutes ago</span>
                                       </a>
                                   </li>
                                   <li>
                                       <a href="#" class="clearfix">
                                           <div class="image">
                                               <i class="fas fa-signal bg-success text-light"></i>
                                           </div>
                                           <span class="title">Connection Restaured</span>
                                           <span class="message">10/10/2021</span>
                                       </a>
                                   </li>
                               </ul>

                               <hr />

                               <div class="text-end">
                                   <a href="#" class="view-more">View All</a>
                               </div>
                           </div>
                       </div>
                   </li>
               </ul>

               <span class="separator"></span>


               <div id="userbox" class="userbox">
                   <a href="#" data-bs-toggle="dropdown">
                       <!-- <figure class="profile-picture">
                           <img src="<?= assets(); ?>/img/!logged-user.jpg" alt="<?= $data['usuario']['nombre_solo'] ?>" class="rounded-circle" data-lock-picture="<?= assets(); ?>/img/!logged-user.jpg" />
                       </figure> -->

                       <div class="profile-info" data-lock-name="<?= $data['usuario']['nombre_solo'] ?>" data-lock-email="j<?= $data['usuario']['email'] ?>">
                           <span class="name"><?= $data['usuario']['nombre_solo'] ?></span>
                           <span class="role"><?= $data['usuario']['rol'] ?></span>
                       </div>

                       <i class="fa custom-caret"></i>
                   </a>

                   <div class="dropdown-menu">
                       <ul class="list-unstyled mb-2">
                           <li class="divider"></li>
                           <!-- <li>
                               <a role="menuitem" tabindex="-1" href="<?= base_url(); ?>/usuarios/perfil"><i class="fa-regular fa-circle-user"></i> Mi Perfil</a>
                           </li> -->
                           <li>
                               <a role="menuitem" tabindex="-1" href="<?= base_url(); ?>/usuarios/cambiarpassword"><i class="fa-regular fa-user-lock"></i> Contraseña</a>
                           </li>
                           <li>

                               <a role="menuitem" tabindex="-1" href="<?= base_url(); ?>/logout"><i class="fa-regular fa-arrow-up-left-from-circle"></i> Salir</a>
                           </li>
                       </ul>
                   </div>
               </div>

           </div>
           <!-- end: search & user box -->
       </header>
       <!-- end: header -->