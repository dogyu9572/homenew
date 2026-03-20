<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubController extends Controller
{
    public function homepage_seo_geo()
    {
        $gNum = "01"; $sNum = "01"; $gName = "Service"; $sName = "SEO·GEO 최적화"; $gSlug = 'service';
        return view('service.homepage-seo-geo', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function homepage_development()
    {
        $gNum = "01"; $sNum = "02"; $gName = "Service"; $sName = "홈페이지 제작"; $gSlug = 'service';
        return view('service.homepage-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function website_maintenance()
    {
        $gNum = "01"; $sNum = "03"; $gName = "Service"; $sName = "홈페이지 유지보수"; $gSlug = 'service';
        return view('service.website-maintenance', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function ecommerce_website_development()
    {
        $gNum = "01"; $sNum = "04"; $gName = "Service"; $sName = "온라인 쇼핑몰 제작"; $gSlug = 'service';
        return view('service.ecommerce-website-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function integrated_si_system_development()
    {
        $gNum = "01"; $sNum = "05"; $gName = "Service"; $sName = "통합 SI 시스템 개발"; $gSlug = 'service';
        return view('service.integrated-si-system-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function mobile_app_development()
    {
        $gNum = "01"; $sNum = "06"; $gName = "Service"; $sName = "앱 개발"; $gSlug = 'service';
        return view('service.mobile-app-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function ai_solution()
    {
        $gNum = "01"; $sNum = "07"; $gName = "Service"; $sName = "맞춤형 AI 솔루션"; $gSlug = 'service';
        return view('service.ai-solution', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
	
    public function enterprise()
    {
        $gNum = "02"; $sNum = "01"; $gName = "Industry"; $sName = "중견/대기업"; $gSlug = 'industries';
        return view('industries.enterprise', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function academic_association()
    {
        $gNum = "02"; $sNum = "02"; $gName = "Industry"; $sName = "학회/협회"; $gSlug = 'industries';
        return view('industries.academic-association', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function government()
    {
        $gNum = "02"; $sNum = "03"; $gName = "Industry"; $sName = "공공기관"; $gSlug = 'industries';
        return view('industries.government', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function hospital_medical_website_development()
    {
        $gNum = "02"; $sNum = "04"; $gName = "Industry"; $sName = "병원/의료"; $gSlug = 'industries';
        return view('industries.hospital-medical-website-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function university_research_lab_website()
    {
        $gNum = "02"; $sNum = "05"; $gName = "Industry"; $sName = "대학·연구실"; $gSlug = 'industries';
        return view('industries.university-research-lab-website', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
	
    public function portfolio_list()
    {
        $gNum = "03"; $sNum = "01"; $gName = "Portfolio"; $sName = "포트폴리오"; $gSlug = 'portfolio';
        return view('portfolio.index', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function portfolio_view()
    {
        $gNum = "03"; $sNum = "01"; $gName = "Portfolio"; $sName = "포트폴리오 상세"; $page = "view"; $gSlug = 'portfolio';
        return view('portfolio.view', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'page'));
    }
	
    public function blog_list()
    {
        $gNum = "04"; $sNum = "01"; $gName = "Blog"; $sName = "블로그"; $gSlug = 'blog';
        return view('blog.index', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
    public function blog_view()
    {
        $gNum = "04"; $sNum = "01"; $gName = "Blog"; $sName = "블로그 상세"; $page = "view"; $gSlug = 'blog';
        return view('blog.view', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'page'));
    }
	
    public function contact()
    {
        $gNum = "05"; $sNum = "01"; $gName = "Contact Us"; $sName = "문의하기"; $gSlug = 'contact';
        return view('contact.index', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
}