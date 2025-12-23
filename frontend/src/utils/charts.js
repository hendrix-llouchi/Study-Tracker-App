/**
 * Get Chart.js configuration with design system colors
 */
export function getChartConfig() {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'top',
        labels: {
          usePointStyle: true,
          padding: 15,
          font: {
            family: 'Inter',
            size: 12
          }
        }
      },
      tooltip: {
        backgroundColor: 'rgba(0, 0, 0, 0.8)',
        padding: 12,
        titleFont: {
          family: 'Inter',
          size: 14,
          weight: '600'
        },
        bodyFont: {
          family: 'Inter',
          size: 12
        },
        cornerRadius: 8,
        displayColors: true
      }
    },
    scales: {
      x: {
        grid: {
          display: false
        },
        ticks: {
          font: {
            family: 'Inter',
            size: 11
          }
        }
      },
      y: {
        grid: {
          color: '#E5E7EB'
        },
        ticks: {
          font: {
            family: 'Inter',
            size: 11
          }
        }
      }
    }
  }
}

/**
 * Design system colors for charts
 */
export const chartColors = {
  primary: '#10B981', // primary-green
  secondary: '#3B82F6', // secondary-blue
  accent: {
    orange: '#F59E0B',
    red: '#EF4444',
    purple: '#8B5CF6'
  },
  neutral: {
    gray50: '#F9FAFB',
    gray100: '#F3F4F6',
    gray200: '#E5E7EB',
    gray300: '#D1D5DB',
    gray400: '#9CA3AF',
    gray500: '#6B7280'
  }
}

/**
 * Generate color palette for charts
 */
export function generateColorPalette(count) {
  const colors = [
    chartColors.primary,
    chartColors.secondary,
    chartColors.accent.orange,
    chartColors.accent.purple,
    chartColors.accent.red
  ]
  
  const palette = []
  for (let i = 0; i < count; i++) {
    palette.push(colors[i % colors.length])
  }
  return palette
}

