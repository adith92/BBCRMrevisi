import React from 'react';
import { BarChart3 } from 'lucide-react';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer
} from 'recharts';

interface ChartData {
  name: string;
  revenue: number; // nilai dalam satuan juta (misal: 320 untuk 320jt)
}

const data: ChartData[] = [
  { name: 'Rata-rata Harian', revenue: 320 },
  { name: 'Kamis (Puncak)', revenue: 740 }
];

export default function WeeklyRevenueChart() {
  // Tick Formatter untuk Y-Axis: mengubah nilai numerik ke format "X Jt"
  const formatYAxis = (value: number) => {
    return `${value} Jt`;
  };

  return (
    <div className="bg-[#111827] border border-slate-800 rounded-2xl p-6 shadow-xl text-white flex flex-col h-full min-h-[380px]">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
        <div className="flex items-center gap-3">
          <BarChart3 className="w-6 h-6 text-sky-400" />
          <h2 className="text-sm font-semibold tracking-wider text-slate-200 uppercase">
            PERGERAKAN REVENUE MINGGUAN
          </h2>
        </div>
        <div className="flex items-center gap-2 text-xs">
          <span className="text-slate-400">Puncak: Kamis - Corporate Airport Transfer</span>
          <button className="text-sky-400 hover:text-sky-300 font-medium transition-colors">
            Detail →
          </button>
        </div>
      </div>

      {/* Chart Area */}
      <div className="flex-1 w-full min-h-[260px]">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart
            data={data}
            margin={{ top: 10, right: 10, left: -10, bottom: 5 }}
          >
            {/* Neon Blue to Navy Gradient definition */}
            <defs>
              <linearGradient id="blueGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stopColor="#38BDF8" stopOpacity={0.9} />  {/* Neon Blue */}
                <stop offset="100%" stopColor="#1E3A8A" stopOpacity={0.3} /> {/* Navy/Blue Dark */}
              </linearGradient>
            </defs>

            {/* Grid Horizontal saja */}
            <CartesianGrid
              vertical={false}
              stroke="#1E293B"
              strokeDasharray="3 3"
              opacity={0.5}
            />

            <XAxis
              dataKey="name"
              stroke="#64748B"
              fontSize={12}
              tickLine={false}
              axisLine={false}
              dy={10}
            />

            <YAxis
              stroke="#64748B"
              fontSize={12}
              tickLine={false}
              axisLine={false}
              domain={[0, 800]}
              tickFormatter={formatYAxis}
              dx={-5}
            />

            {/* Styled Tooltip */}
            <Tooltip
              cursor={{ fill: 'rgba(255, 255, 255, 0.03)' }}
              contentStyle={{
                backgroundColor: '#1E293B',
                borderColor: '#334155',
                borderRadius: '12px',
                color: '#F8FAFC'
              }}
              formatter={(value: number) => [`Rp ${value.toLocaleString('id-ID')} Jt`, 'Revenue']}
              labelStyle={{ color: '#94A3B8', fontWeight: 'bold', fontSize: '12px' }}
            />

            {/* Rounded Bars with Gradient */}
            <Bar
              dataKey="revenue"
              fill="url(#blueGradient)"
              radius={[6, 6, 0, 0]}
              barSize={38}
            />
          </BarChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
