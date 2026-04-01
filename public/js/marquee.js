// ① 데이터 풀 (전체 관리)
const MARQUEE_DATA = {
//중견/대기업
    a: [
        { src: "/images/logo_service_a01.svg", alt: "KB부동산신탁", title: "KB부동산신탁" },
		{ src: "/images/logo_service_a02.svg", alt: "교보자산신탁", title: "교보자산신탁" },
		{ src: "/images/logo_service_a03.svg", alt: "교보AIM자산운용", title: "교보AIM자산운용" },
		{ src: "/images/logo_service_a04.svg", alt: "한국오므론제어기기", title: "한국오므론제어기기" },
		{ src: "/images/logo_service_a05.png", alt: "KOREAN AIR", title: "KOREAN AIR" },
		{ src: "/images/logo_service_a06.png", alt: "GS에너지", title: "GS에너지" },
		{ src: "/images/logo_service_a07.svg", alt: "GS엔텍", title: "GS엔텍" },
		{ src: "/images/logo_service_a08.svg", alt: "GS Global", title: "GS Global" },
		{ src: "/images/logo_service_a09.svg", alt: "GS파워", title: "GS파워" },
		{ src: "/images/logo_service_a10.png", alt: "한미사이언스", title: "한미사이언스" },
		{ src: "/images/logo_service_a11.png", alt: "DB 그룹", title: "DB 그룹" },
		{ src: "/images/logo_service_a12.svg", alt: "BYD", title: "BYD" },
		{ src: "/images/logo_service_a13.png", alt: "HLB글로벌(주)", title: "HLB글로벌(주)" },
		{ src: "/images/logo_service_a14.svg", alt: "SGC그린파워", title: "SGC그린파워" },
		{ src: "/images/logo_service_a15.png", alt: "S-에너지", title: "S-에너지" },
		{ src: "/images/logo_service_a16.svg", alt: "계양전기", title: "계양전기" },
		{ src: "/images/logo_service_a17.png", alt: "리레코코리아", title: "리레코코리아" },
		{ src: "/images/logo_service_a18.png", alt: "미래생활", title: "미래생활" },
		{ src: "/images/logo_service_a19.png", alt: "삼광랩트리", title: "삼광랩트리" },
		{ src: "/images/logo_service_a20.svg", alt: "에코아이디", title: "에코아이디" },
		{ src: "/images/logo_service_a21.png", alt: "엠투아이코퍼레이션", title: "엠투아이코퍼레이션" },
		{ src: "/images/logo_service_a22.png", alt: "오리온", title: "오리온" },
		{ src: "/images/logo_service_a23.png", alt: "COSMOJIN", title: "COSMOJIN" },
		{ src: "/images/logo_service_a24.svg", alt: "COWAY", title: "COWAY" },
		{ src: "/images/logo_service_a25.svg", alt: "ADT캡스", title: "ADT캡스" },
		{ src: "/images/logo_service_a26.png", alt: "트리니티소프트", title: "트리니티소프트" },
		{ src: "/images/logo_service_a27.png", alt: "틴매일경제", title: "틴매일경제" },
		{ src: "/images/logo_service_a28.svg", alt: "파크랜드", title: "파크랜드" },
		{ src: "/images/logo_service_a29.svg", alt: "피엘에스", title: "피엘에스" },
		{ src: "/images/logo_service_a30.svg", alt: "한국삼공", title: "한국삼공" },
		{ src: "/images/logo_service_a31.png", alt: "더존", title: "더존" },
		{ src: "/images/logo_service_a32.png", alt: "네오팜", title: "네오팜" },
		{ src: "/images/logo_service_a33.png", alt: "에스엘엠메디트리", title: "에스엘엠메디트리" },
		{ src: "/images/logo_service_a34.png", alt: "프레시지", title: "프레시지" },
		{ src: "/images/logo_service_a35.png", alt: "시공사", title: "시공사" },
		{ src: "/images/logo_service_a36.svg", alt: "한국철근거래소", title: "한국철근거래소" },
		{ src: "/images/logo_service_a37.png", alt: "LF푸드", title: "LF푸드" },
		{ src: "/images/logo_service_a38.png", alt: "한국마쯔다니주식회사", title: "한국마쯔다니주식회사" },
		{ src: "/images/logo_service_a39.svg", alt: "건원엔지니어링", title: "건원엔지니어링" },
		{ src: "/images/logo_service_a40.svg", alt: "하이얼코리아", title: "하이얼코리아" },
		{ src: "/images/logo_service_a41.svg", alt: "글로넷홀딩스", title: "글로넷홀딩스" },
		{ src: "/images/logo_service_a42.png", alt: "밥스누", title: "밥스누" },
		{ src: "/images/logo_service_a43.png", alt: "SGC E&C", title: "SGC E&C" },
		{ src: "/images/logo_service_a44.png", alt: "AJ네트웍스", title: "AJ네트웍스" },
		{ src: "/images/logo_service_a45.png", alt: "유니코정밀화학", title: "유니코정밀화학" },
		{ src: "/images/logo_service_a46.png", alt: "후지테크코리아", title: "후지테크코리아" },
		{ src: "/images/logo_service_a47.png", alt: "대우컴퓨레셔", title: "대우컴퓨레셔" },
		{ src: "/images/logo_service_a48.png", alt: "에즈웰플러스", title: "에즈웰플러스" }
    ],
//학회/협회
    b: [
        { src: "/images/logo_service_b01.svg", alt: "기후에너지환경부", title: "기후에너지환경부" },
		{ src: "/images/logo_service_b02.svg", alt: "행정안전부", title: "행정안전부" },
		{ src: "/images/logo_service_b03.svg", alt: "과학기술정보통신부", title: "과학기술정보통신부" },
		{ src: "/images/logo_service_b04.svg", alt: "국민체육진흥공단", title: "국민체육진흥공단" },
		{ src: "/images/logo_service_b05.svg", alt: "여성기업종합지원센터", title: "여성기업종합지원센터" },
		{ src: "/images/logo_service_b06.svg", alt: "ICT CoC", title: "ICT CoC" },
		{ src: "/images/logo_service_b07.png", alt: "국민영금기금운용본부", title: "국민영금기금운용본부" },
		{ src: "/images/logo_service_b08.svg", alt: "국립스포츠박물관", title: "국립스포츠박물관" },
		{ src: "/images/logo_service_b09.svg", alt: "금융감독원", title: "금융감독원" },
		{ src: "/images/logo_service_b10.svg", alt: "대한민국시도지사협의회", title: "대한민국시도지사협의회" },
		{ src: "/images/logo_service_b11.svg", alt: "문화체육관광부", title: "문화체육관광부" },
		{ src: "/images/logo_service_b12.svg", alt: "신용보증기금", title: "신용보증기금" },
		{ src: "/images/logo_service_b13.png", alt: "KETI", title: "KETI" },
		{ src: "/images/logo_service_b14.png", alt: "UNPOG", title: "UNPOG" },
		{ src: "/images/logo_service_b15.png", alt: "e순환거버넌스", title: "e순환거버넌스" },
		{ src: "/images/logo_service_b16.svg", alt: "인천광역시 서구 녹청자박물관", title: "인천광역시 서구 녹청자박물관" },
		{ src: "/images/logo_service_b17.png", alt: "서울시 통합건강증진사업지원단", title: "서울시 통합건강증진사업지원단" },
		{ src: "/images/logo_service_b18.svg", alt: "인천광역시 감염병관리지원단", title: "인천광역시 감염병관리지원단" },
		{ src: "/images/logo_service_b19.png", alt: "인터넷우체국", title: "인터넷우체국" },
		{ src: "/images/logo_service_b20.svg", alt: "KIAPS", title: "KIAPS" },
		{ src: "/images/logo_service_b21.svg", alt: "한국국토정보공사", title: "한국국토정보공사" },
		{ src: "/images/logo_service_b22.png", alt: "KARC", title: "KARC" },
		{ src: "/images/logo_service_b23.svg", alt: "한국순환자원유통지원센터", title: "한국순환자원유통지원센터" },
		{ src: "/images/logo_service_b24.png", alt: "전쟁기념사업회", title: "전쟁기념사업회" },
		{ src: "/images/logo_service_b25.svg", alt: "KTOA", title: "KTOA" },
		{ src: "/images/logo_service_b26.svg", alt: "화성시장애인체육회", title: "화성시장애인체육회" },
		{ src: "/images/logo_service_b27.svg", alt: "화성반다비체육센터", title: "화성반다비체육센터" },
		{ src: "/images/logo_service_b28.png", alt: "삼청각", title: "삼청각" },
		{ src: "/images/logo_service_b29.png", alt: "훈선시정신건강복지센터", title: "훈선시정신건강복지센터" },
		{ src: "/images/logo_service_b30.png", alt: "영등포구다문화가족지원센터", title: "영등포구다문화가족지원센터" },
		{ src: "/images/logo_service_b31.png", alt: "영등포구가족센터", title: "영등포구가족센터" },
		{ src: "/images/logo_service_b32.png", alt: "한국수출입은행", title: "한국수출입은행" },
		{ src: "/images/logo_service_b33.svg", alt: "우체국금융개발원", title: "우체국금융개발원" },
		{ src: "/images/logo_service_b34.png", alt: "다가치", title: "다가치" },
		{ src: "/images/logo_service_b35.png", alt: "사랑의열매", title: "사랑의열매" },
		{ src: "/images/logo_service_b36.png", alt: "화성FC", title: "화성FC" },
		{ src: "/images/logo_service_b37.svg", alt: "한국거래소 파생결합증권 통합정보플랫폼", title: "한국거래소 파생결합증권 통합정보플랫폼" },
		{ src: "/images/logo_service_b38.png", alt: "한국거래소 ESG포털", title: "한국거래소 ESG포털" },
		{ src: "/images/logo_service_b39.png", alt: "한국거래소", title: "한국거래소" },
		{ src: "/images/logo_service_b40.png", alt: "한국그리드스마트사업단", title: "한국그리드스마트사업단" },
		{ src: "/images/logo_service_b41.png", alt: "농림수산업자신용보증기금", title: "농림수산업자신용보증기금" },
    ],
//공공기관
    c: [
		{ src: "/images/logo_service_c01.png", alt: "가톨릭대학교", title: "가톨릭대학교" },
		{ src: "/images/logo_service_c02.svg", alt: "강동성심병원", title: "강동성심병원" },
		{ src: "/images/logo_service_c03.svg", alt: "고신대학교 복음병원 임상시험센터", title: "고신대학교 복음병원 임상시험센터" },
		{ src: "/images/logo_service_c04.svg", alt: "고신대학교 복음병원", title: "고신대학교 복음병원" },
		{ src: "/images/logo_service_c05.png", alt: "김천의료원", title: "김천의료원" },
		{ src: "/images/logo_service_c06.svg", alt: "안동의료원", title: "안동의료원" },
		{ src: "/images/logo_service_c07.svg", alt: "포항의료원", title: "포항의료원" },
		{ src: "/images/logo_service_c08.png", alt: "고려대학교 안암병원", title: "고려대학교 안암병원" },
		{ src: "/images/logo_service_c09.svg", alt: "유메디", title: "유메디" },
		{ src: "/images/logo_service_c10.svg", alt: "유투바이오", title: "유투바이오" },
		{ src: "/images/logo_service_c11.png", alt: "제이시스메디칼", title: "제이시스메디칼" },
		{ src: "/images/logo_service_c12.png", alt: "KIOS", title: "KIOS" },
		{ src: "/images/logo_service_c13.png", alt: "서울아산병원", title: "서울아산병원" },
		{ src: "/images/logo_service_c14.png", alt: "고려대학교구로병원_R&D사업", title: "고려대학교구로병원_R&D사업" },
		{ src: "/images/logo_service_c15.png", alt: "신원의료재단", title: "신원의료재단" },
		{ src: "/images/logo_service_c16.png", alt: "유투의료재단", title: "유투의료재단" },
		{ src: "/images/logo_service_c17.png", alt: "유투바이옴솔루션", title: "유투바이옴솔루션" },
		{ src: "/images/logo_service_c18.png", alt: "트리니움여성병원", title: "트리니움여성병원" },
		{ src: "/images/logo_service_c19.png", alt: "경동제약", title: "경동제약" },
		{ src: "/images/logo_service_c20.svg", alt: "인성의료재단 한림병원", title: "인성의료재단 한림병원" },
		{ src: "/images/logo_service_c21.svg", alt: "인성의료재단 채용정보센터", title: "인성의료재단 채용정보센터" },
		{ src: "/images/logo_service_c22.png", alt: "중앙보훈병원", title: "중앙보훈병원" },
		{ src: "/images/logo_service_c23.png", alt: "기린통증의학과", title: "기린통증의학과" },
		{ src: "/images/logo_service_c24.png", alt: "덕수의료재단", title: "덕수의료재단" },
		{ src: "/images/logo_service_c25.svg", alt: "이왕병원", title: "이왕병원" },
		{ src: "/images/logo_service_c26.png", alt: "명지병원", title: "명지병원" },
		{ src: "/images/logo_service_c27.png", alt: "세바른병원", title: "세바른병원" },
		{ src: "/images/logo_service_c28.png", alt: "창원요양병원", title: "창원요양병원" }
	],
//병원/의료
    d: [
		{ src: "/images/logo_service_d01.svg", alt: "서울대학교 농생명과학공동기기원", title: "서울대학교 농생명과학공동기기원" },
		{ src: "/images/logo_service_d02.png", alt: "서울대학교공동기기원", title: "서울대학교공동기기원" },
		{ src: "/images/logo_service_d03.svg", alt: "한양대학교", title: "한양대학교" },
		{ src: "/images/logo_service_d04.png", alt: "서울대학교 산학협력단", title: "서울대학교 산학협력단" },
		{ src: "/images/logo_service_d05.svg", alt: "세종대학교 공동기기원", title: "세종대학교 공동기기원" },
		{ src: "/images/logo_service_d06.svg", alt: "숭실대학교 글로벌미래교욱원", title: "숭실대학교 글로벌미래교욱원" },
		{ src: "/images/logo_service_d07.png", alt: "교육행정연수원", title: "교육행정연수원" },
		{ src: "/images/logo_service_d08.svg", alt: "고려대학교 대외협력처", title: "고려대학교 대외협력처" },
		{ src: "/images/logo_service_d09.svg", alt: "연세대학교 탄소절감연구소", title: "연세대학교 탄소절감연구소" },
		{ src: "/images/logo_service_d10.png", alt: "비상", title: "비상" },
		{ src: "/images/logo_service_d11.png", alt: "잉글리시아이", title: "잉글리시아이" },
		{ src: "/images/logo_service_d12.png", alt: "고려대학교의료원 대외협력실", title: "고려대학교의료원 대외협력실" },
		{ src: "/images/logo_service_d13.png", alt: "현재어학원", title: "현재어학원" },
		{ src: "/images/logo_service_d14.svg", alt: "유켄영국유학", title: "유켄영국유학" },
		{ src: "/images/logo_service_d15.png", alt: "열림대방고시학원", title: "열림대방고시학원" },
		{ src: "/images/logo_service_d16.png", alt: "리라초등학교", title: "리라초등학교" }
	],
//대학·연구실
    e: [
		{ src: "/images/logo_service_e01.png", alt: "대한안과의사회", title: "대한안과의사회" },
		{ src: "/images/logo_service_e02.svg", alt: "시스템반도체 개발지원센터", title: "시스템반도체 개발지원센터" },
		{ src: "/images/logo_service_e03.png", alt: "KTOA 벤처리움", title: "KTOA 벤처리움" },
		{ src: "/images/logo_service_e04.svg", alt: "여성기업종합정보포털", title: "여성기업종합정보포털" },
		{ src: "/images/logo_service_e05.svg", alt: "SGC문화재단", title: "SGC문화재단" },
		{ src: "/images/logo_service_e06.png", alt: "경기도청소년상담복지센터", title: "경기도청소년상담복지센터" },
		{ src: "/images/logo_service_e07.png", alt: "대한심혈관중재학회", title: "대한심혈관중재학회" },
		{ src: "/images/logo_service_e08.svg", alt: "대한고혈압학회", title: "대한고혈압학회" },
		{ src: "/images/logo_service_e09.svg", alt: "대한심부전학회 KSHF", title: "대한심부전학회 KSHF" },
		{ src: "/images/logo_service_e10.svg", alt: "한국AI로봇산업협회", title: "한국AI로봇산업협회" },
		{ src: "/images/logo_service_e11.svg", alt: "한국심초음파학회", title: "한국심초음파학회" },
		{ src: "/images/logo_service_e12.png", alt: "한국녹내장학회", title: "한국녹내장학회" },
		{ src: "/images/logo_service_e13.svg", alt: "한국지질동맥경화학회", title: "한국지질동맥경화학회" },
		{ src: "/images/logo_service_e14.png", alt: "한국심초음파학회(영문)", title: "한국심초음파학회(영문)" },
		{ src: "/images/logo_service_e15.png", alt: "한국고분자학회", title: "한국고분자학회" },
		{ src: "/images/logo_service_e16.png", alt: "한국금융소비자보호재단", title: "한국금융소비자보호재단" },
		{ src: "/images/logo_service_e17.svg", alt: "아셈", title: "아셈" },
		{ src: "/images/logo_service_e18.svg", alt: "인하우스카운슬포럼", title: "인하우스카운슬포럼" },
		{ src: "/images/logo_service_e19.svg", alt: "푸른나무재단", title: "푸른나무재단" },
		{ src: "/images/logo_service_e20.png", alt: "한국폐기물협회", title: "한국폐기물협회" },
		{ src: "/images/logo_service_e21.png", alt: "대한상공회의소", title: "대한상공회의소" },
		{ src: "/images/logo_service_e22.svg", alt: "한국의학바이오기자협회", title: "한국의학바이오기자협회" },
		{ src: "/images/logo_service_e23.png", alt: "IROC", title: "IROC" },
		{ src: "/images/logo_service_e24.png", alt: "대한지방행정공제회", title: "대한지방행정공제회" },
		{ src: "/images/logo_service_e25.png", alt: "군포시노동종합복지관", title: "군포시노동종합복지관" },
		{ src: "/images/logo_service_e26.png", alt: "성산포수산업협동조합", title: "성산포수산업협동조합" },
		{ src: "/images/logo_service_e27.png", alt: "아셈중소기업혁신센터", title: "아셈중소기업혁신센터" },
		{ src: "/images/logo_service_e28.svg", alt: "한국폐기물협회 분리의정석", title: "한국폐기물협회 분리의정석" },
		{ src: "/images/logo_service_e29.svg", alt: "화성시립미술관국제지명설계공모", title: "화성시립미술관국제지명설계공모" },
		{ src: "/images/logo_service_e30.png", alt: "한국건설교통신기술협회", title: "한국건설교통신기술협회" },
		{ src: "/images/logo_service_e31.png", alt: "한국경량기포콘크리트협회", title: "한국경량기포콘크리트협회" },
		{ src: "/images/logo_service_e32.png", alt: "한국과학창의재단", title: "한국과학창의재단" },
		{ src: "/images/logo_service_e33.png", alt: "한국인정평가원", title: "한국인정평가원" },
		{ src: "/images/logo_service_e34.png", alt: "해양레저스포츠", title: "해양레저스포츠" }
	],
//기타
    f: [
		{ src: "/images/logo_service_f01.svg", alt: "로보월드", title: "로보월드" },
		{ src: "/images/logo_service_f02.svg", alt: "루틴7", title: "루틴7" },
		{ src: "/images/logo_service_f03.svg", alt: "개디질개선 서울국제포럼", title: "개디질개선 서울국제포럼" },
		{ src: "/images/logo_service_f04.png", alt: "ib2", title: "ib2" },
		{ src: "/images/logo_service_f05.png", alt: "유니크투어", title: "유니크투어" },
		{ src: "/images/logo_service_f06.png", alt: "스포츠토토", title: "스포츠토토" }
	]
};
	
function getRandomItems(dataKeys, count) {
	const combined = dataKeys.flatMap(key => MARQUEE_DATA[key] ?? []);
	return combined.sort(() => Math.random() - 0.5).slice(0, count);
}

// ② 공통 함수
function initMarquee(selector, items, options = {}) {
    const $banner = $(selector);
    if (!$banner.length || !items?.length) return;

    const isMobile = window.innerWidth < 768;
    const speed = isMobile
        ? (options.mobileSpeed ?? options.speed ?? 3)
        : (options.speed ?? 2);
	
    let posX = 0;

    // 배열 랜덤 섞기 (Fisher-Yates 알고리즘)
    const shuffled = [...items].sort(() => Math.random() - 0.5);

    const $slide = $('<ul class="slide" aria-label="주요 고객사 목록"></ul>');
    shuffled.forEach(function (item) {
        $slide.append(`<li><img src="${item.src}" alt="${item.alt}" title="${item.title}"></li>`);
    });
    $banner.append($slide);
    $banner.append($slide.clone().removeAttr("aria-label").attr("aria-hidden", "true"));

    $(window).on("load", function () {
        const totalWidth = $slide.outerWidth(true);
        function marqueeLoop() {
            posX -= speed;
            if (Math.abs(posX) >= totalWidth) posX = 0;
            $banner.find(".slide").css("transform", `translateX(${posX}px)`);
            requestAnimationFrame(marqueeLoop);
        }
        marqueeLoop();
    });
	
}

//사용 예시
//일반
//initMarquee("#marquee_banner_a", MARQUEE_DATA.a);
//랜덤
//initMarquee("#marquee_banner_random", getRandomItems(['a','b','c','d','e'], 40));