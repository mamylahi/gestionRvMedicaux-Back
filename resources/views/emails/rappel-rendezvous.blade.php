<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .info-box {
            background-color: white;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 15px 0;
        }
        .info-label {
            font-weight: bold;
            color: #4CAF50;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Rappel de Rendez-vous</h2>
    </div>

    <div class="content">
        <p>Bonjour <strong>{{ $rendezVous->patient->user->nom }} {{ $rendezVous->patient->user->prenom }}</strong>,</p>

        <p>Nous vous rappelons que vous avez un rendez-vous médical <strong>demain</strong>.</p>

        <div class="info-box">
            <p><span class="info-label">📅 Date :</span> {{ \Carbon\Carbon::parse($rendezVous->date_rendezvous)->format('d/m/Y') }}</p>
            <p><span class="info-label">🕐 Heure :</span> {{ \Carbon\Carbon::parse($rendezVous->heure_rendezvous)->format('H:i') }}</p>
            <p><span class="info-label">👨‍⚕️ Médecin :</span> Dr. {{ $rendezVous->medecin->user->nom }} {{ $rendezVous->medecin->user->prenom }}</p>
            @if($rendezVous->medecin->specialite)
                <p><span class="info-label">🏥 Spécialité :</span> {{ $rendezVous->medecin->specialite->nom }}</p>
            @endif
            @if($rendezVous->motif)
                <p><span class="info-label">📝 Motif :</span> {{ $rendezVous->motif }}</p>
            @endif
        </div>

        <p>Merci de respecter l'horaire de votre rendez-vous.</p>
        <p>En cas d'empêchement, veuillez nous contacter pour annuler ou reporter.</p>
    </div>

    <div class="footer">
        <p>Cet email est un rappel automatique.</p>
        <p>&copy; {{ date('Y') }} - Votre Cabinet Médical</p>
    </div>
</div>
</body>
</html>
