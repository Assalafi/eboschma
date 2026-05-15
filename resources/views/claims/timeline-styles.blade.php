<style>
    /* Modern Timeline Styling */
    .timeline {
        position: relative;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 36px;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #e9ecef 0%, #dee2e6 100%);
    }

    .timeline-item {
        position: relative;
        padding-left: 50px;
        padding-bottom: 30px;
    }

    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .timeline-marker:hover {
        transform: scale(1.1);
    }

    .timeline-content {
        padding: 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .timeline-content:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .timeline-item.completed .timeline-marker {
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .timeline-item.rejected .timeline-marker {
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .timeline-item.pending .timeline-marker {
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }
</style>
