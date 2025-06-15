<?php
/**
 * IT Admin Tools
 *
 * A collection of utilities and references for IT professionals.
 */

// Page variables
$page_title = "IT Admin Tools - ZeroNexus";
$page_description = "Essential utilities and references for IT professionals.";
$page_css = "/css/network-admin.css";
$page_js = "/js/network-admin.js";
$page_js_type = "module"; // Using ES modules for JavaScript
$header_title = "IT Admin Tools";
$header_subtitle = "Essential utilities for IT professionals";

// Add Prism.js for syntax highlighting
$extra_scripts = '
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-bash.min.js"></script>
';

// Define component paths
$components_path = __DIR__;
$tabs_path = $components_path . '/tabs/';
$modals_path = $components_path . '/modals/';
$shared_path = $components_path . '/shared/';

// Include header
include 'includes/header.php';
?>

<main>
    <div class="container">
        <!-- Tool search bar -->
        <?php include $shared_path . 'search.php'; ?>

        <!-- Tab navigation -->
        <?php include $shared_path . 'navigation.php'; ?>
        
        <!-- Tab content -->
        <div class="tab-content" id="toolTabsContent">
            <!-- All Tools -->
            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                <?php include $tabs_path . 'all.php'; ?>
            </div>

            <!-- Diagnostic Tools -->
            <div class="tab-pane fade" id="diagnostics" role="tabpanel" aria-labelledby="diagnostics-tab">
                <?php include $tabs_path . 'diagnostics.php'; ?>
            </div>

            <!-- Security Tools -->
            <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                <?php include $tabs_path . 'security.php'; ?>
            </div>

            <!-- Command References -->
            <div class="tab-pane fade" id="commands" role="tabpanel" aria-labelledby="commands-tab">
                <?php include $tabs_path . 'commands.php'; ?>
            </div>

            <!-- Reference Guides -->
            <div class="tab-pane fade" id="references" role="tabpanel" aria-labelledby="references-tab">
                <?php include $tabs_path . 'references.php'; ?>
            </div>

            <!-- Calculators and Converters -->
            <div class="tab-pane fade" id="calculators" role="tabpanel" aria-labelledby="calculators-tab">
                <?php include $tabs_path . 'calculators.php'; ?>
            </div>

            <!-- Templates -->
            <div class="tab-pane fade" id="templates" role="tabpanel" aria-labelledby="templates-tab">
                <?php include $tabs_path . 'templates.php'; ?>
            </div>
        </div>
    </div>
</main>

<!-- Modal Components -->
<?php 
// Include all modal components
include $modals_path . 'subnet-calculator.php';
include $modals_path . 'dns-lookup.php';
include $modals_path . 'ping-traceroute.php';
include $modals_path . 'security-headers.php';
include $modals_path . 'security-headers-generator.php';
include $modals_path . 'security-headers-help.php';
include $modals_path . 'password-strength.php';
include $modals_path . 'firewall-rule-generator.php';
include $modals_path . 'linux-commands.php';
include $modals_path . 'windows-commands.php';
?>

<?php
// Include footer
include 'includes/footer.php';
?>