-- ============================================================
-- Migration v2 - Corrections FaMaKo (28/05/2026)
-- ============================================================

-- CHANGEMENT 3 : Corriger l'historique
-- Supprimer les doublons (entrées 6 à 10)
DELETE FROM historique WHERE id IN (6, 7, 8, 9, 10);

-- Corriger l'année 2019 → 2026 pour le lancement DSPR
UPDATE historique SET annee = '2026' WHERE id = 2 AND titre_fr LIKE '%DSPR%';

-- Vérification
-- SELECT id, annee, titre_fr FROM historique ORDER BY ordre;
