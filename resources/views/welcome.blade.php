<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G-Luper | Modern Learning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 antialiased">

    <!-- Header -->
    <header class="fixed top-6 inset-x-0 z-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center bg-white/70 backdrop-blur-xl border border-white/20 shadow-[0_8px_32px_0_rgba(31,38,135,0.07)] rounded-2xl px-6 py-3">
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20 group-hover:scale-105 transition-transform">
                        <span class="text-white font-bold text-lg">G</span>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-slate-800">Luper</span>
                </a>

                <div class="flex items-center gap-6">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition">Sign in</a>
                    <a href="{{ route('register') }}" class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md hover:shadow-blue-500/25 hover:-translate-y-0.5 transition-all">
                        Join for Free
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-6">
            
            <section class="py-16 text-center">
                <div class="inline-flex items-center gap-2 py-1 px-3 rounded-full bg-indigo-50 text-indigo-600 text-[12px] font-bold uppercase tracking-wider mb-8">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                    </span>
                    System Now Live - Batch 1 Complete
                </div>
                
                <h1 class="text-6xl md:text-8xl font-black text-slate-900 tracking-tight leading-[0.9] mb-8">
                    Live Classes.<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600">Real Growth.</span>
                </h1>
                
                <p class="text-xl text-slate-600 max-w-2xl mx-auto leading-relaxed mb-10 font-medium">
                    Stop learning in isolation. Join a cohort, attend live hands-on sessions, and master tech with a global community. 
                </p>
                
                <div class="flex flex-col sm:flex-row gap-5 justify-center">
                    <a href="{{ route('register') }}" class="bg-slate-900 text-white px-10 py-5 rounded-2xl font-bold text-lg hover:bg-indigo-600 transition-all shadow-2xl shadow-indigo-200 inline-flex items-center justify-center gap-2 group">
                        Get Started Now
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    <a href="{{ route('login') }}" class="bg-white border-2 border-slate-200 text-slate-900 px-10 py-5 rounded-2xl font-bold text-lg hover:border-indigo-600 hover:text-indigo-600 transition-all shadow-sm">
                        Sign In
                    </a>
                </div>
            </section>

            <!-- Feature Cards -->
            <section class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-8 bg-indigo-600 rounded-[2.5rem] text-white">
                    <div class="text-3xl mb-4">🔐</div>
                    <h4 class="text-xl font-bold mb-2">Secure Authentication</h4>
                    <p class="text-indigo-100 text-sm leading-relaxed">Enterprise-grade security with role-based access control and audit logging.</p>
                </div>
                <div class="p-8 bg-white border border-slate-200 rounded-[2.5rem]">
                    <div class="text-3xl mb-4">👥</div>
                    <h4 class="text-xl font-bold mb-2">Multi-Role System</h4>
                    <p class="text-slate-500 text-sm leading-relaxed">Support for Admins, Mentors, and Learners with custom permissions.</p>
                </div>
                <div class="p-8 bg-slate-900 rounded-[2.5rem] text-white">
                    <div class="text-3xl mb-4">📊</div>
                    <h4 class="text-xl font-bold mb-2">Complete Audit Trail</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">Track every action for compliance and security monitoring.</p>
                </div>
            </section>

            <!-- System Status -->
            <section class="mt-32">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-[3rem] p-12 lg:p-16 border border-blue-100">
                    <div class="text-center max-w-3xl mx-auto">
                        <div class="inline-flex items-center gap-2 bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-bold mb-6">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            System Status: Operational
                        </div>
                        
                        <h2 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 leading-tight">
                            Foundation & Authentication <br>
                            <span class="text-indigo-600">Complete ✓</span>
                        </h2>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-12">
                            <div class="bg-white rounded-2xl p-6 shadow-sm">
                                <div class="text-3xl font-black text-indigo-600 mb-2">4</div>
                                <div class="text-sm text-slate-600 font-semibold">User Roles</div>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-sm">
                                <div class="text-3xl font-black text-indigo-600 mb-2">100%</div>
                                <div class="text-sm text-slate-600 font-semibold">Security</div>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-sm">
                                <div class="text-3xl font-black text-indigo-600 mb-2">∞</div>
                                <div class="text-sm text-slate-600 font-semibold">Audit Logs</div>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-sm">
                                <div class="text-3xl font-black text-indigo-600 mb-2">0</div>
                                <div class="text-sm text-slate-600 font-semibold">Vulnerabilities</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="mt-32">
                <div class="bg-slate-900 rounded-[3.5rem] p-12 lg:p-24 text-center relative overflow-hidden">
                    <div class="relative z-10">
                        <h2 class="text-4xl md:text-6xl font-black text-white mb-8 leading-[1.1]">
                            Ready to begin? <br> 
                            <span class="text-indigo-400">Create your account.</span>
                        </h2>
                        <p class="text-slate-400 text-lg mb-12 max-w-xl mx-auto">
                            Join the platform with enterprise-grade security and role-based access control.
                        </p>
                        <div class="flex flex-wrap justify-center gap-6">
                            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-12 py-5 rounded-2xl font-bold text-lg hover:scale-105 transition-all shadow-xl shadow-indigo-500/20">
                                Create Free Account
                            </a>
                            <a href="{{ route('login') }}" class="bg-white/5 text-white border border-white/10 px-12 py-5 rounded-2xl font-bold backdrop-blur-md hover:bg-white/10 transition-all">
                                Sign In
                            </a>
                        </div>
                    </div>
                    
                    <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600/20 rounded-full blur-[100px]"></div>
                    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-purple-600/20 rounded-full blur-[100px]"></div>
                </div>
            </section>

        </div>
    </main>

    <!-- Footer -->
    <footer class="max-w-7xl mx-auto px-6 py-12 border-t border-slate-200 mt-20">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-sm text-slate-500">© {{ date('Y') }} G-Luper Learning. Built with enterprise-grade security.</p>
            <div class="flex gap-8">
                <a href="#" class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-blue-600 transition">Privacy</a>
                <a href="#" class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-blue-600 transition">Terms</a>
                <a href="#" class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-blue-600 transition">Support</a>
            </div>
        </div>
    </footer>

</body>
</html>