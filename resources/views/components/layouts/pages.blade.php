<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>34 Teknik Bercinta — Long-Form Sales Page</title>
    <meta name="description" content="Hidupkan semula rasa cinta dengan 34 teknik bercinta yang mudah, murah dan ikhlas oleh Kamalia Kamal. Long-form sales page dengan CTA" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ruby: '#8b0000',
                        maroon: '#5c0a14',
                        rose: '#d1264f',
                        blush: '#ec4899',
                        orchid: '#6d28d9',
                        mulberry: '#581c54',
                        champagne: '#f7e7ce',
                        cream: '#fef9f3',
                        midnight: '#1e1b4b',
                        pearl: '#f8fafc',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'ui-sans-serif', 'system-ui'],
                        display: ['Playfair Display', 'ui-serif', 'Georgia']
                    },
                    boxShadow: {
                        'glow': '0 20px 60px rgba(139,0,0,.4), 0 8px 25px rgba(139,0,0,.25)',
                        'soft': '0 16px 48px rgba(109,40,217,.2), 0 6px 20px rgba(109,40,217,.15)',
                        'inset': 'inset 0 1px 0 rgba(255,255,255,.3), inset 0 -1px 0 rgba(0,0,0,.08)',
                        'luxury': '0 25px 75px rgba(88,28,84,.3), 0 10px 30px rgba(88,28,84,.2)',
                        'elegant': '0 12px 40px rgba(92,10,20,.18), 0 4px 15px rgba(92,10,20,.12)',
                        'dreamy': '0 20px 65px rgba(236,72,153,.25), 0 8px 25px rgba(236,72,153,.15)'
                    },
                    backgroundImage: {
                        'royal-gradient': 'linear-gradient(135deg, #5c0a14 0%, #8b0000 25%, #d1264f 50%, #ec4899 75%, #6d28d9 100%)',
                        'luxury-gradient': 'linear-gradient(120deg, #1e1b4b 0%, #581c54 30%, #5c0a14 60%, #8b0000 100%)',
                        'champagne-gradient': 'linear-gradient(180deg, #fef9f3 0%, #f7e7ce 50%, #fef9f3 100%)',
                        'pearl-shimmer': 'linear-gradient(45deg, #f8fafc 0%, #ffffff 25%, #f8fafc 50%, #ffffff 75%, #f8fafc 100%)'
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        /* Enhanced Background Patterns */
        .bg-velvet {
            background:
                radial-gradient(1400px 700px at 20% 10%, rgba(236,72,153,.12), transparent 65%),
                radial-gradient(1200px 800px at 80% 5%, rgba(109,40,217,.15), transparent 70%),
                radial-gradient(1000px 650px at 50% 95%, rgba(139,0,0,.18), transparent 75%),
                linear-gradient(180deg, #fef9f3 0%, #f8fafc 15%, #ffffff 30%, #f7e7ce 65%, #fef9f3 100%);
        }

        .hero {
            background:
                radial-gradient(ellipse 1200px 600px at 0% 0%, rgba(109,40,217,.25), transparent 50%),
                radial-gradient(ellipse 1000px 500px at 100% 0%, rgba(236,72,153,.2), transparent 50%),
                linear-gradient(135deg, #1e1b4b 0%, #581c54 25%, #5c0a14 50%, #8b0000 75%, #d1264f 100%);
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle 800px at 25% 25%, rgba(236,72,153,.15), transparent 60%),
                radial-gradient(circle 600px at 75% 75%, rgba(109,40,217,.12), transparent 50%);
            pointer-events: none;
        }

        /* Glass & Material Effects */
        .glass {
            backdrop-filter: blur(20px) saturate(180%);
            background: linear-gradient(135deg,
            rgba(255,255,255,.95) 0%,
            rgba(255,255,255,.85) 50%,
            rgba(248,250,252,.90) 100%);
            border: 1px solid rgba(255,255,255,.3);
        }

        .glass-dark {
            backdrop-filter: blur(16px) saturate(150%);
            background: linear-gradient(135deg,
            rgba(30,27,75,.85) 0%,
            rgba(88,28,84,.80) 50%,
            rgba(92,10,20,.85) 100%);
            border: 1px solid rgba(255,255,255,.15);
        }

        /* Enhanced Buttons */
        .btn-cta {
            background: linear-gradient(90deg, #5c0a14 0%, #8b0000 25%, #d1264f 50%, #ec4899 75%, #6d28d9 100%);
            background-size: 300% 100%;
            transition: all .8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }

        .btn-cta::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.2) 50%, transparent 100%);
            transform: translateX(-100%);
            transition: transform .6s ease;
        }

        .btn-cta:hover {
            background-position: 100% 0;
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 25px 50px rgba(139,0,0,.4), 0 10px 30px rgba(139,0,0,.3);
        }

        .btn-cta:hover::before {
            transform: translateX(100%);
        }

        /* Premium Cards */
        .badge {
            background: linear-gradient(135deg,
            rgba(255,255,255,.95) 0%,
            rgba(247,231,206,.90) 50%,
            rgba(255,255,255,.85) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.4);
            box-shadow: 0 8px 25px rgba(92,10,20,.15), inset 0 1px 0 rgba(255,255,255,.5);
        }

        .rad-card {
            background:
                radial-gradient(600px 300px at 0% 0%, rgba(236,72,153,.06), transparent 60%),
                radial-gradient(500px 250px at 100% 100%, rgba(109,40,217,.06), transparent 60%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(209,38,79,.08);
            box-shadow: 0 12px 40px rgba(88,28,84,.08), 0 4px 15px rgba(88,28,84,.05);
            transition: all .4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .rad-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 60px rgba(88,28,84,.15), 0 8px 25px rgba(88,28,84,.1);
            border-color: rgba(209,38,79,.15);
        }

        /* Luxury Borders */
        .luxury-border {
            border: 2px solid transparent;
            background: linear-gradient(white, white) padding-box,
            linear-gradient(135deg, #ec4899, #6d28d9, #5c0a14) border-box;
        }

        /* Text Enhancements */
        .text-gradient {
            background: linear-gradient(135deg, #5c0a14 0%, #8b0000 50%, #d1264f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Smooth Animations */
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Selection */
        ::selection {
            background: linear-gradient(135deg, rgba(236,72,153,.3), rgba(109,40,217,.3));
            color: #5c0a14;
        }
    </style>
</head>
<body class="font-sans text-slate-800 bg-white selection:bg-blush/50 selection:text-maroon">
    {{ $slot }}
</body>
</html>
