<?php // includes/footer.php ?>
<footer class="site-footer">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <div class="footer-brand mb-3">FaMaKo <span>Bangui</span></div>
        <p class="footer-desc">
          <span data-lang="fr">Faculté Maïngo Ködörö — Sciences de l'Éducation. Centre d'excellence au service du développement de la République Centrafricaine.</span>
          <span data-lang="en">Maïngo Ködörö Faculty — Educational Sciences. Center of excellence for the development of the Central African Republic.</span>
        </p>
        <div class="social-links mt-4">
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="https://www.youtube.com/@famako" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
      </div>

      <div class="col-6 col-md-4 col-lg-2">
        <h6 class="footer-heading">Navigation</h6>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
          <li><a href="<?= BASE_URL ?>/pages/presentation.php">Présentation</a></li>
          <li><a href="<?= BASE_URL ?>/pages/historique.php">Histoire</a></li>
          <li><a href="<?= BASE_URL ?>/pages/cours.php">Cours</a></li>
          <li><a href="<?= BASE_URL ?>/pages/td.php">Travaux Dirigés</a></li>
          <li><a href="<?= BASE_URL ?>/pages/inscriptions.php">Inscriptions</a></li>
          <li><a href="<?= BASE_URL ?>/pages/contact.php">Contact</a></li>
        </ul>
      </div>

      <div class="col-6 col-md-4 col-lg-2">
        <h6 class="footer-heading">Formation</h6>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/pages/inscription.php">DSPR</a></li>
          <li><a href="<?= BASE_URL ?>/pages/inscription.php">Doctorat</a></li>
          <li><a href="<?= BASE_URL ?>/pages/cours.php">13 Disciplines</a></li>
          <li><a href="<?= BASE_URL ?>/pages/inscriptions.php">Frais & Paiement</a></li>
          <li><a href="<?= BASE_URL ?>/pages/inscription.php">S'inscrire</a></li>
        </ul>
      </div>

   <div class="col-6 col-md-4 col-lg-2">
        <h6 class="footer-heading">Ressources</h6>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/pages/cours.php">Cours PDF/Vidéo</a></li>
          <li><a href="<?= BASE_URL ?>/pages/historique.php">Histoire Faculté</a></li>
          <li>
            <a href="https://www.youtube.com/@famako" target="_blank">
              <i class="fab fa-youtube me-2" style="color:#ff0000"></i>Chaîne YouTube
            </a>
          </li>
          <li style="margin-top:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,.08);">
            <a href="https://us06web.zoom.us/j/87948815783?pwd=p2fjRPConAr2gkbrJFFPzBb2BmaxAz.1" target="_blank">
              <i class="fas fa-video me-2" style="color:#2D8CFF"></i>Cours en ligne Zoom
            </a>
          </li>
          <li style="font-size:.75rem;opacity:.6;padding-left:1.4rem;">
            Lun · Mer · Sam — 18h00 (Bangui)<br>
            ID: 879 4881 5783 · Code: 014224
          </li>
        </ul>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <h6 class="footer-heading">Contact</h6>
        <ul class="footer-links">
          <li><a href="#"><i class="fas fa-map-marker-alt me-2 text-accent"></i>Bangui, RCA</a></li>
          <li><a href="mailto:fmaingokodoro@gmail.com"><i class="fas fa-envelope me-2 text-accent"></i>FaMaKo</a></li>
          <li><a href="#"><i class="fas fa-clock me-2 text-accent"></i>Lun–Ven 8h–17h</a></li>
          <!-- Lien admin discret -->
          <li style="margin-top:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,.08);">
            <a href="<?= BASE_URL ?>/admin/login.php" style="opacity:.5;font-size:.78rem;">
              <i class="fas fa-lock me-2"></i>Administration
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© <?= date('Y') ?> Faculté Maïngo Ködörö — Tous droits réservés</span>
      <div class="d-flex gap-3 flex-wrap">
        <a href="#">Mentions légales</a>
        <a href="#">Confidentialité</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
