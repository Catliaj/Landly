<?php

use App\Controllers\Admin\DashboardController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit tests for Admin Dashboard Controller structure and initialization
 * These tests validate the controller can be instantiated and has proper dependencies
 * without requiring database connections.
 */
final class AdminDashboardControllerStructureTest extends CIUnitTestCase
{
    /**
     * Test: Controller can be instantiated
     */
    public function testAdminDashboardControllerCanBeInstantiated(): void
    {
        try {
            $controller = new DashboardController();
            $this->assertInstanceOf(DashboardController::class, $controller);
        } catch (\CodeIgniter\Exceptions\CriticalError $e) {
            if (str_contains($e->getMessage(), 'sqlite3')) {
                $this->markTestSkipped('SQLite3 extension not available. Database tests skipped.');
            }
            throw $e;
        }
    }

    /**
     * Test: Controller has required model dependencies
     */
    public function testControllerHasRequiredModels(): void
    {
        try {
            $controller = new DashboardController();
        } catch (\CodeIgniter\Exceptions\CriticalError $e) {
            if (str_contains($e->getMessage(), 'sqlite3')) {
                $this->markTestSkipped('SQLite3 extension not available. Database tests skipped.');
            }
            throw $e;
        }
        
        // Use reflection to verify protected properties exist
        $reflection = new ReflectionClass($controller);
        
        $this->assertTrue($reflection->hasProperty('userModel'), 'Controller should have userModel property');
        $this->assertTrue($reflection->hasProperty('listingModel'), 'Controller should have listingModel property');
        $this->assertTrue($reflection->hasProperty('reportsModel'), 'Controller should have reportsModel property');
        $this->assertTrue($reflection->hasProperty('sellerVerificationModel'), 'Controller should have sellerVerificationModel property');
        $this->assertTrue($reflection->hasProperty('listingImagesModel'), 'Controller should have listingImagesModel property');
    }

    /**
     * Test: All model properties are properly initialized
     */
    public function testModelsAreInitializedInConstructor(): void
    {
        try {
            $controller = new DashboardController();
        } catch (\CodeIgniter\Exceptions\CriticalError $e) {
            if (str_contains($e->getMessage(), 'sqlite3')) {
                $this->markTestSkipped('SQLite3 extension not available. Database tests skipped.');
            }
            throw $e;
        }
        
        $userModelReflection = new ReflectionProperty($controller, 'userModel');
        $userModelReflection->setAccessible(true);
        
        $listingModelReflection = new ReflectionProperty($controller, 'listingModel');
        $listingModelReflection->setAccessible(true);
        
        $reportsModelReflection = new ReflectionProperty($controller, 'reportsModel');
        $reportsModelReflection->setAccessible(true);
        
        $sellerVerificationModelReflection = new ReflectionProperty($controller, 'sellerVerificationModel');
        $sellerVerificationModelReflection->setAccessible(true);
        
        $listingImagesModelReflection = new ReflectionProperty($controller, 'listingImagesModel');
        $listingImagesModelReflection->setAccessible(true);
        
        $this->assertNotNull($userModelReflection->getValue($controller), 'userModel should be initialized');
        $this->assertNotNull($listingModelReflection->getValue($controller), 'listingModel should be initialized');
        $this->assertNotNull($reportsModelReflection->getValue($controller), 'reportsModel should be initialized');
        $this->assertNotNull($sellerVerificationModelReflection->getValue($controller), 'sellerVerificationModel should be initialized');
        $this->assertNotNull($listingImagesModelReflection->getValue($controller), 'listingImagesModel should be initialized');
    }

    /**
     * Test: Controller has an index method
     */
    public function testControllerHasIndexMethod(): void
    {
        $reflection = new ReflectionClass(DashboardController::class);
        $this->assertTrue($reflection->hasMethod('index'), 'Controller should have index method');
    }

    /**
     * Test: Index method is public
     */
    public function testIndexMethodIsPublic(): void
    {
        $reflection = new ReflectionMethod(DashboardController::class, 'index');
        $this->assertTrue($reflection->isPublic(), 'Index method should be public');
    }

    /**
     * Test: Controllers imports all required models
     */
    public function testImportsRequiredNamespaces(): void
    {
        $filePath = APPPATH . 'Controllers/Admin/DashboardController.php';
        $this->assertFileExists($filePath, 'DashboardController file should exist');
        
        $fileContent = file_get_contents($filePath);
        
        $this->assertStringContainsString('use App\Models\UserModel', $fileContent, 'Should import UserModel');
        $this->assertStringContainsString('use App\Models\LandListings', $fileContent, 'Should import LandListings');
        $this->assertStringContainsString('use App\Models\ReportsModel', $fileContent, 'Should import ReportsModel');
        $this->assertStringContainsString('use App\Models\SellerVerificationModel', $fileContent, 'Should import SellerVerificationModel');
        $this->assertStringContainsString('use App\Models\ListingImages', $fileContent, 'Should import ListingImages');
    }

    /**
     * Test: Dashboard view file exists
     */
    public function testDashboardViewFileExists(): void
    {
        $viewPath = APPPATH . 'Views/Pages/Admin/Dashboard_Admin.php';
        $this->assertFileExists($viewPath, 'Dashboard_Admin view should exist');
    }

    /**
     * Test: All component files exist
     */
    public function testComponentFilesExist(): void
    {
        $components = [
            'UserSection.php',
            'ListingSection.php',
            'SellerSection.php',
            'ReportSection.php',
        ];
        
        $componentPath = APPPATH . 'Views/Pages/Admin/Components/';
        
        foreach ($components as $component) {
            $fullPath = $componentPath . $component;
            $this->assertFileExists($fullPath, "Component {$component} should exist");
        }
    }

    /**
     * Test: DashboardController doesn't have console.log statements
     */
    public function testNoConsoleLogStatementsInDashboard(): void
    {
        $dashboardPath = APPPATH . 'Views/Pages/Admin/Dashboard_Admin.php';
        $dashboardContent = file_get_contents($dashboardPath);
        
        $this->assertStringNotContainsString('console.log(', $dashboardContent, 'Dashboard should not contain console.log statements');
        $this->assertStringNotContainsString('console.error(', $dashboardContent, 'Dashboard should not contain console.error statements');
    }

    /**
     * Test: Seller section has profile picture URL field
     */
    public function testSellerSectionHasProfilePictureLogic(): void
    {
        $sellerSectionPath = APPPATH . 'Views/Pages/Admin/Components/SellerSection.php';
        $sellerSectionContent = file_get_contents($sellerSectionPath);
        
        $this->assertStringContainsString('profile_picture_url', $sellerSectionContent, 'SellerSection should use profile_picture_url');
    }

    /**
     * Test: Listing section has image display logic
     */
    public function testListingImageDisplayLogic(): void
    {
        $listingSectionPath = APPPATH . 'Views/Pages/Admin/Components/ListingSection.php';
        $listingSectionContent = file_get_contents($listingSectionPath);
        
        $this->assertStringContainsString('image_url', $listingSectionContent, 'ListingSection should use image_url field');
        $this->assertStringContainsString('<img', $listingSectionContent, 'ListingSection should display images with img tag');
    }

    /**
     * Test: User model has all required fields
     */
    public function testUserModelHasRequiredFields(): void
    {
        $userModelPath = APPPATH . 'Models/UserModel.php';
        $userModelContent = file_get_contents($userModelPath);
        
        $requiredFields = [
            'first_name',
            'last_name',
            'email',
            'profile_picture',
            'is_active',
            'roles',
        ];
        
        foreach ($requiredFields as $field) {
            $this->assertStringContainsString("'" . $field . "'", $userModelContent, "UserModel should allow {$field}");
        }
    }

    /**
     * Test: Reports model has required fields
     */
    public function testReportsModelHasRequiredFields(): void
    {
        $reportsModelPath = APPPATH . 'Models/ReportsModel.php';
        $reportsModelContent = file_get_contents($reportsModelPath);
        
        $requiredFields = [
            'reported_by',
            'reported_against',
            'subject',
            'status',
        ];
        
        foreach ($requiredFields as $field) {
            $this->assertStringContainsString("'" . $field . "'", $reportsModelContent, "ReportsModel should allow {$field}");
        }
    }

    /**
     * Test: Seller verification model has required fields
     */
    public function testSellerVerificationModelHasRequiredFields(): void
    {
        $sellerVerificationPath = APPPATH . 'Models/SellerVerificationModel.php';
        if (!file_exists($sellerVerificationPath)) {
            $this->markTestSkipped('SellerVerificationModel file not found');
        }
        
        $sellerVerificationContent = file_get_contents($sellerVerificationPath);
        
        $requiredFields = [
            'seller_id',
            'is_verified',
        ];
        
        foreach ($requiredFields as $field) {
            $this->assertStringContainsString("'" . $field . "'", $sellerVerificationContent, "SellerVerificationModel should allow {$field}");
        }
    }

    /**
     * Test: Image path resolution uses base_url()
     */
    public function testImageResolutionUsesBaseUrl(): void
    {
        $dashboardControllerPath = APPPATH . 'Controllers/Admin/DashboardController.php';
        $dashboardContent = file_get_contents($dashboardControllerPath);
        
        $this->assertStringContainsString('base_url(', $dashboardContent, 'Controller should use base_url() for image path resolution');
    }

    /**
     * Test: SVG fallback is generated for listings without images
     */
    public function testSvgPlaceholderGenerationForListings(): void
    {
        $dashboardControllerPath = APPPATH . 'Controllers/Admin/DashboardController.php';
        $dashboardContent = file_get_contents($dashboardControllerPath);
        
        $this->assertStringContainsString('data:image/svg+xml', $dashboardContent, 'Controller should generate SVG placeholders for listings');
    }

    /**
     * Test: Report data is enriched with user names
     */
    public function testReportEnrichmentLogicExists(): void
    {
        $dashboardControllerPath = APPPATH . 'Controllers/Admin/DashboardController.php';
        $dashboardContent = file_get_contents($dashboardControllerPath);
        
        $this->assertStringContainsString('reported_by_name', $dashboardContent, 'Controller should create reported_by_name field');
        $this->assertStringContainsString('reported_against_name', $dashboardContent, 'Controller should create reported_against_name field');
    }

    /**
     * Test: User verification status enrichment logic exists
     */
    public function testUserVerificationStatusEnrichmentLogicExists(): void
    {
        $dashboardControllerPath = APPPATH . 'Controllers/Admin/DashboardController.php';
        $dashboardContent = file_get_contents($dashboardControllerPath);
        
        $this->assertStringContainsString('verification_status', $dashboardContent, 'Controller should create verification_status field');
    }

    /**
     * Test: User reports count enrichment logic exists
     */
    public function testUserReportCountEnrichmentLogicExists(): void
    {
        $dashboardControllerPath = APPPATH . 'Controllers/Admin/DashboardController.php';
        $dashboardContent = file_get_contents($dashboardControllerPath);
        
        $this->assertStringContainsString('reports_filed', $dashboardContent, 'Controller should create reports_filed field');
        $this->assertStringContainsString('reports_against', $dashboardContent, 'Controller should create reports_against field');
    }

    /**
     * Test: Dashboard passes data to view
     */
    public function testDashboardPassesDataToView(): void
    {
        $dashboardControllerPath = APPPATH . 'Controllers/Admin/DashboardController.php';
        $dashboardContent = file_get_contents($dashboardControllerPath);
        
        $this->assertStringContainsString("view('Pages/Admin/Dashboard_Admin'", $dashboardContent, 'Controller should pass data to Dashboard_Admin view');
    }
}
