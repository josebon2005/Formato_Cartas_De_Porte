<style>
    .pagos-section { margin-bottom: 20px; }
    .pagos-section h2 { font-size: 19px; margin: 0 0 10px; }
    .pagos-section-head { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; margin-bottom: 14px; }
    .pagos-section-head h2 { margin: 0; }
    .pagos-filter { align-items: end; display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
    .pagos-filter > div { flex: 1 1 150px; }
    .pagos-filter .actions { flex: 0 1 auto; }
    .pagos-number { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .pagos-editor { min-width: 1080px; }
    .pagos-editor td { padding: 9px 6px; }
    .pagos-editor input, .pagos-editor select { min-width: 105px; }
    .pagos-editor input[type="date"] { min-width: 145px; }
    .pagos-editor .pagos-text { min-width: 150px; }
    .pagos-check { align-items: center; display: inline-flex; gap: 6px; margin: 8px 0 0; white-space: nowrap; }
    .pagos-check input[type="checkbox"] { accent-color: var(--accent); min-height: 20px; min-width: 20px; width: 20px; }
    .pagos-disabled { color: var(--muted); }
    .pagos-disabled input:not([type="checkbox"]), .pagos-disabled select { opacity: .65; }
    .pagos-summary { margin-left: auto; max-width: 460px; }
    .pagos-summary dl { margin: 0; }
    .pagos-summary dl > div { align-items: baseline; display: flex; gap: 16px; justify-content: space-between; padding: 9px 0; }
    .pagos-summary dt, .pagos-summary dd { margin: 0; }
    .pagos-summary dd { font-variant-numeric: tabular-nums; white-space: nowrap; }
    .pagos-summary .pagos-grand-total { border-top: 2px solid var(--accent); font-size: 23px; font-weight: 800; margin-top: 8px; padding-top: 16px; }
    .pagos-metadata { margin-bottom: 18px; }
    .pagos-metadata strong { display: block; margin-bottom: 5px; }
    .pagos-note { white-space: pre-wrap; overflow-wrap: anywhere; }
    .pagos-total-viajes { font-size: 17px; font-weight: 700; margin: 16px 0 0; text-align: right; }
    .pagos-actions { align-items: center; }
    .pagos-actions form { margin: 0; }
    @media (max-width: 680px) {
        .pagos-section { padding: 14px; }
        .pagos-summary { max-width: none; }
        .pagos-summary .pagos-grand-total { font-size: 20px; }
        .pagos-metadata { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pagos-metadata > div { overflow-wrap: anywhere; }
        .pagos-actions { flex-wrap: wrap; }
    }
</style>
