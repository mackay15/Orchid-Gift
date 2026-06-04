<?php
// includes/footer.php - Global Page Footer
$base = "/ORCHID";
?>
    </main> <!-- End of main tag -->
    
    <footer class="pt-5 pb-3">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-3 brand-font"><i class="bi bi-flower1 me-1 text-secondary"></i> ORCHID</h5>
                    <p class="text-muted small">Where Every Gift Tells a Story. We customize and deliver memorable gifts, exquisite flowers, luxury hampers, and unique personalized items for all occasions.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-muted fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-muted fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-muted fs-5"><i class="bi bi-whatsapp"></i></a>
                        <a href="#" class="text-muted fs-5"><i class="bi bi-envelope-fill"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-2"><a href="<?php echo $base; ?>/index.php">Home</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/customer/shop.php">Browse Gifts</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/login.php">Portal Sign In</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/register.php">Create Account</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white mb-3">Top Gift Categories</h6>
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-2"><a href="<?php echo $base; ?>/customer/shop.php?category=1">Midnight Flowers</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/customer/shop.php?category=2">Artisanal Chocolates</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/customer/shop.php?category=4">Luxury Hampers</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/customer/shop.php?category=5">Customized Keepsakes</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6 class="text-white mb-3">Shop Address</h6>
                    <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill me-2 text-primary"></i> 45 Orchid Garden St, POS Plaza, Suite B</p>
                    <p class="text-muted small mb-2"><i class="bi bi-telephone-fill me-2 text-primary"></i> +1 (233) 555-ORCHID</p>
                    <p class="text-muted small mb-2"><i class="bi bi-clock-fill me-2 text-primary"></i> Mon - Sat: 8:00 AM - 9:00 PM</p>
                </div>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.08);">
            
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <p class="text-muted small mb-0">&copy; <?php echo date('Y'); ?> ORCHID GIFT AND MORE. All rights reserved.</p>
                <span class="badge bg-orchid-grad p-2 px-3 brand-font" style="font-size: 0.8rem; letter-spacing: 0.5px;">Motto: Where Every Gift Tells a Story</span>
            </div>
        </div>
    </footer>
</div> <!-- End of wrapper -->

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
