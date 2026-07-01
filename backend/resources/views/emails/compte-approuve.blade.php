<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Compte GPI approuvé</title></head>
<body style="font-family:sans-serif;max-width:500px;margin:40px auto;color:#1a1a2e">
  <h2 style="color:#073b67">Bienvenue sur GPI 🎉</h2>
  <p>Bonjour <strong>{{ $demande->prenom }} {{ $demande->nom }}</strong>,</p>
  <p>Votre demande d'accès a été approuvée. Voici vos identifiants de connexion :</p>
  <div style="background:#f4f7fb;border-radius:8px;padding:16px 24px;margin:24px 0">
    <p><strong>URL :</strong> <a href="http://localhost:5173/login">http://localhost:5173/login</a></p>
    <p><strong>E-mail :</strong> {{ $demande->email }}</p>
    <p><strong>Mot de passe :</strong> <code style="background:#e2e8f0;padding:2px 8px;border-radius:4px">{{ $motDePasse }}</code></p>
    <p><strong>Rôle :</strong> {{ $demande->role }}</p>
  </div>
  <p style="color:#888;font-size:13px">Nous vous recommandons de changer votre mot de passe après la première connexion.</p>
  <p>— L'équipe GPI</p>
</body>
</html>